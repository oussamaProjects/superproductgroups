<?php

namespace PrestaShop\Module\SuperProductGroups\Controller\Admin;

use PrestaShop\Module\SuperProductGroups\Form\Type\GroupFormType;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SuperProductGroupsController extends FrameworkBundleAdminController
{
    /**
     * @Route("/superproductgroups/handle-form-submit", name="superproductgroups_handle_form_submit", methods={"POST"})
     */
    public function handleFormSubmit(Request $request): JsonResponse
    {
        $superProductId = (int)$request->request->get('id_super_product');

        if (!$superProductId) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid product ID'], 400);
        }

        $form = $this->createForm(GroupFormType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid form submission'], 400);
        }

        $data = $form->getData();

        $submittedGroupIds = array_filter(array_column($data['groups'], 'group_id'));
        $existingGroupIds = $this->getExistingGroupIds();

        $this->handleGroupDeletions($existingGroupIds, $submittedGroupIds);
        $this->saveOrUpdateGroups($data['groups'], $superProductId);

        return new JsonResponse(['status' => 'success', 'message' => 'Groups saved successfully']);
    }

    /**
     * Deletes groups and their associated product relationships.
     */
    private function handleGroupDeletions(array $existingGroupIds, array $submittedGroupIds): void
    {
        $groupsToDelete = array_diff($existingGroupIds, $submittedGroupIds);

        if (!empty($groupsToDelete)) {
            $db = \Db::getInstance();
            $ids = implode(',', array_map('intval', $groupsToDelete));

            $db->delete('product_group_relationship', 'id_group IN (' . $ids . ')');
            $db->delete('product_group', 'id_group IN (' . $ids . ')');
        }
    }

    /**
     * Saves or updates groups and their associated products.
     */
    private function saveOrUpdateGroups(array $groups, int $superProductId): void
    {
        foreach ($groups as $groupData) {
            $groupId = $groupData['group_id'] ?? null;
            $groupName = $groupData['group_name'];
            $groupImage = $groupData['group_image'] ?? null;
            $groupProducts = $groupData['group_products'];

            $imagePath = $groupImage instanceof UploadedFile ? $this->uploadImage($groupImage) : null;

            print_r( 'groupProducts');
            print_r( $groupProducts);
            $groupId = $this->saveOrUpdateGroup($groupId, $groupName, $imagePath, $superProductId);
            $this->saveOrUpdateProductAssociations($groupId, $groupProducts);
        }
    }

    /**
     * Uploads the provided image file.
     */
    private function uploadImage(UploadedFile $file): string
    {
        $uploadDir = _PS_IMG_DIR_ . 'groups/';
        $fileName = uniqid() . '.' . $file->guessExtension();
        $file->move($uploadDir, $fileName);

        return _PS_IMG_ . 'groups/' . $fileName;
    }

    /**
     * Saves or updates a group and returns its ID.
     */
    private function saveOrUpdateGroup(?int $groupId, string $name, ?string $imagePath, int $superProductId): int
    {
        $db = \Db::getInstance();

        if ($groupId) {
            $db->update('product_group', [
                'name' => pSQL($name),
                'image' => pSQL($imagePath),
            ], 'id_group = ' . (int)$groupId);

            return $groupId;
        }

        $db->insert('product_group', [
            'id_super_product' => (int)$superProductId,
            'name' => pSQL($name),
            'image' => pSQL($imagePath),
        ]);

        return (int)$db->Insert_ID();
    }

    /**
     * Saves or updates associations between the group and selected products.
     */
    private function saveOrUpdateProductAssociations(int $groupId, array $groupProducts): void
    {
        $db = \Db::getInstance();

        $existingProductIds = array_column(
            $db->executeS(
                (new \DbQuery())
                    ->select('id_product')
                    ->from('product_group_relationship')
                    ->where('id_group = ' . (int)$groupId)
            ),
            'id_product'
        );

        $this->syncProductAssociations($groupId, $existingProductIds, $groupProducts);
    }

    private function syncProductAssociations(int $groupId, array $existingProductIds, array $newProductIds): void
    {
        $db = \Db::getInstance();
        print_r( 'existingProductIds');
        print_r( $existingProductIds);
        print_r( 'newProductIds');
        print_r( $newProductIds);
        $productsToRemove = array_diff($existingProductIds, $newProductIds);
        $productsToAdd = array_diff($newProductIds, $existingProductIds);
print_r( 'productsToRemove');
print_r( $productsToRemove);
print_r( 'productsToAdd');
print_r( $productsToAdd);
        if (!empty($productsToRemove)) {
            $db->delete('product_group_relationship', 'id_group = ' . (int)$groupId . ' AND id_product IN (' . implode(',', $productsToRemove) . ')');
        }

        if (!empty($productsToAdd)) {
            $values = array_map(function ($productId) use ($groupId) {
                return ['id_group' => $groupId, 'id_product' => (int)$productId];
            }, $productsToAdd);

            $db->insert('product_group_relationship', $values, false, true);
        }
    }

    /**
     * Fetches existing group IDs from the database.
     */
    private function getExistingGroupIds(): array
    {
        return array_column(
            \Db::getInstance()->executeS(
                (new \DbQuery())
                    ->select('id_group')
                    ->from('product_group')
            ),
            'id_group'
        );
    }

    /**
     * @Route("/superproductgroups/ajax-products", name="admin_superproductgroups_ajax_products", methods={"GET"})
     */
    public function ajaxProducts(Request $request): JsonResponse
    {
        $search = $request->query->get('q', '');
        $languageId = (int)\Context::getContext()->language->id;

        $sql = (new \DbQuery())
            ->select('pl.id_product, pl.name')
            ->from('product_lang', 'pl')
            ->leftJoin('product_group_relationship', 'pgr', 'pl.id_product = pgr.id_product')
            ->where('pgr.id_product IS NULL') // Exclude already grouped products
            ->where('pl.name LIKE "%' . pSQL($search) . '%"')
            ->where('pl.id_lang = ' . $languageId);

        $products = \Db::getInstance()->executeS($sql);

        $results = array_map(function ($product) {
            return ['id' => $product['id_product'], 'text' => $product['name']];
        }, $products);

        return new JsonResponse(['results' => $results]);
    }
}
