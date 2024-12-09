<?php

namespace PrestaShop\Module\SuperProductGroups\Controller\Admin;

use PrestaShop\Module\SuperProductGroups\Form\Type\GroupFormType;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

		// Handle uploaded files for group images
		$groups = $data['groups'];
		foreach ($groups as $key => $group) {
			// Fetch the uploaded file from $request->files
			$uploadedFile = $request->files->get("group_form")['groups'][$key]['group_image'] ?? null;

			if ($uploadedFile instanceof UploadedFile) {
				// Handle file upload
				$imagePath = $this->uploadImage($uploadedFile);
				$groups[$key]['group_image'] = $imagePath; // Update group data with uploaded image path
			} else {
				$groups[$key]['group_image'] = null; // No file uploaded
			}
		}

		$this->saveOrUpdateGroups($groups, $superProductId);

		return new JsonResponse(['status' => 'success', 'message' => 'Groups saved successfully']);
	}


	/**
		* Saves or updates groups and their associated products.
		*/
	private function saveOrUpdateGroups(array $groups, int $superProductId): void
	{

		foreach ($groups as $groupData) {
			$groupId = $groupData['group_id'] ?? null;
			$groupName = $groupData['group_name'];
			$imagePath = $groupData['group_image'] ?? null;
			$groupProducts = $groupData['group_products'] ?? [];

			// $imagePath = $groupImage instanceof UploadedFile ? $this->uploadImage($groupImage) : null;
			$groupId = $this->saveOrUpdateGroup($groupId, $groupName, $imagePath, $superProductId);

			if (!empty($groupProducts)) {
				$this->saveOrUpdateProductAssociations($groupId, $groupProducts);
			}
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
			$updateData = [
				'name' => pSQL($name),
			];

			if (!empty($imagePath)) {
				$updateData['image'] = pSQL($imagePath);
			}

			$db->update('product_group', $updateData, 'id_group = ' . (int)$groupId);

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
		$productsToRemove = array_diff($existingProductIds, $newProductIds);
		$productsToAdd = array_diff($newProductIds, $existingProductIds);
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
		* @Route("/superproductgroups/save-group-products", name="superproductgroups_save_group_products", methods={"POST"})
		*/
	public function saveGroupProducts(Request $request): JsonResponse
	{

		$groupId = (int) $request->request->get('groupId');
		$products = $request->request->get('products', []);

		if (!$groupId || !is_array($products)) {
			return new JsonResponse(['status' => 'error', 'message' => 'Invalid group or products'], 400);
		}

		$db = \Db::getInstance();

		try {

			// Delete existing relationships for the group to ensure clean data
      $db->delete('product_group_relationship', 'id_group = ' . (int) $groupId);

      // Insert the new products with their order
      foreach ($products as $product) {
          if (!isset($product['id']) || !isset($product['order'])) {
              continue; // Skip invalid product data
          }

          $db->insert('product_group_relationship', [
              'id_group' => $groupId,
              'id_product' => (int) $product['id'],
              'product_order' => (int) $product['order'], // Save the order
          ]);
      }

			return new JsonResponse(['status' => 'success', 'message' => 'Products saved successfully']);
		} catch (\Exception $e) {
			return new JsonResponse(['status' => 'error', 'message' => 'Failed to save products: ' . $e->getMessage()], 500);
		}
	}

	/**
		* @Route("/modules/superproductgroups/delete-group", name="superproductgroups_delete_group", methods={"POST"})
		*/
	public function deleteGroup(Request $request): JsonResponse
	{
		$groupId = (int) $request->request->get('groupId');

		// Validate input
		if (!$groupId) {
			return new JsonResponse(['status' => 'error', 'message' => 'Invalid group ID'], 400);
		}

		$db = \Db::getInstance();

		try {

			// Delete all product associations for the group
			$db->delete('product_group_relationship', 'id_group = ' . (int)$groupId);

			// Delete the group itself
			$db->delete('product_group', 'id_group = ' . (int)$groupId);

			return new JsonResponse(['status' => 'success', 'message' => 'Group and its products deleted successfully']);
		} catch (\Exception $e) {
			$db->rollBack();

			// Log the error for debugging
			\PrestaShopLogger::addLog('Error deleting group: ' . $e->getMessage(), 3);

			return new JsonResponse(['status' => 'error', 'message' => 'Failed to delete group: ' . $e->getMessage()], 500);
		}
	}


	/**
		* @Route("/modules/superproductgroups/delete-group-product", name="superproductgroups_delete_group_product", methods={"POST"})
		*/
	public function deleteGroupProduct(Request $request): JsonResponse
	{
		$groupId = (int)$request->request->get('groupId');
		$productId = (int)$request->request->get('productId');

		// Validate inputs
		if (!$groupId || !$productId) {
			return new JsonResponse(['status' => 'error', 'message' => 'Invalid group or product ID'], 400);
		}

		$db = \Db::getInstance();

		try {
			// Delete the specific product from the group
			$db->delete('product_group_relationship', 'id_group = ' . (int)$groupId . ' AND id_product = ' . (int)$productId);

			return new JsonResponse(['status' => 'success', 'message' => 'Product removed from group successfully']);
		} catch (\Exception $e) {
			// Log the error for debugging
			\PrestaShopLogger::addLog('Error deleting product from group: ' . $e->getMessage(), 3);

			return new JsonResponse(['status' => 'error', 'message' => 'Failed to remove product from group: ' . $e->getMessage()], 500);
		}
	}


	/**
		* @Route("/modules/superproductgroups/delete-group-products", name="superproductgroups_delete_group_products", methods={"POST"})
		*/
	public function deleteGroupProducts(Request $request): JsonResponse
	{
		$groupId = (int)$request->request->get('groupId');

		// Validate input
		if (!$groupId) {
			return new JsonResponse(['status' => 'error', 'message' => 'Invalid group ID'], 400);
		}

		$db = \Db::getInstance();

		try {
			// Delete all product associations for the group
			$db->delete('product_group_relationship', 'id_group = ' . (int)$groupId);

			return new JsonResponse(['status' => 'success', 'message' => 'All products removed from group successfully']);
		} catch (\Exception $e) {
			// Log the error for debugging
			\PrestaShopLogger::addLog('Error deleting all products from group: ' . $e->getMessage(), 3);

			return new JsonResponse(['status' => 'error', 'message' => 'Failed to remove all products from group: ' . $e->getMessage()], 500);
		}
	}

	/**
		* @Route("/superproductgroups/ajax-products", name="admin_superproductgroups_ajax_products", methods={"GET"})
		*/
	public function ajaxProducts(Request $request): JsonResponse
	{
		// Get the search query from the request
		$search = $request->query->get('q', '');
		$languageId = (int) \Context::getContext()->language->id;

		// Build the SQL query
		$sql = (new \DbQuery())
			->select('pl.id_product, pl.name')
			->from('product_lang', 'pl')
			->leftJoin('product_group_relationship', 'pgr', 'pl.id_product = pgr.id_product')
			->where('pgr.id_product IS NULL') // Exclude products already in groups
			->where('pl.name LIKE "%' . pSQL($search) . '%"') // Match the search query
			->where('pl.id_lang = ' . $languageId) // Filter by the current language
			->groupBy('pl.id_product') // Ensure no duplicate rows for the same product
      ->orderBy('pgr.product_order ASC');

		// Execute the query and fetch the results
		$products = \Db::getInstance()->executeS($sql);

		// Format the results for the Select2 dropdown
		$results = array_map(function ($product) {
			return [
				'id' => $product['id_product'],
				'text' => $product['name'],
			];
		}, $products);

		// Return the results as a JSON response
		return new JsonResponse(['results' => $results]);
	}

	/**
		* @Route("/superproductgroups/ajax-group-products", name="admin_superproductgroups_ajax_group_products", methods={"GET"})
		*/
	public function ajaxGroupProducts(Request $request): JsonResponse
	{
		$groupId = $request->query->get('group_id');

		// Fetch products for the group
		$sql = new \DbQuery();
		$sql->select('p.id_product, pl.name, ps.price');
		$sql->from('product_group_relationship', 'pgr');
		$sql->innerJoin('product', 'p', 'p.id_product = pgr.id_product');
		$sql->innerJoin('product_lang', 'pl', 'pl.id_product = p.id_product');
		$sql->innerJoin('product_shop', 'ps', 'ps.id_product = p.id_product');
		$sql->where('pgr.id_group = ' . (int) $groupId);
		$sql->where('pl.id_lang = ' . (int) \Context::getContext()->language->id);
    $sql->orderBy('pgr.product_order ASC');

		$products = \Db::getInstance()->executeS($sql);

		return new JsonResponse(['products' => $products]);
	}





	/**
		* @Route("/modules/superproductgroups/export-group-products", name="superproductgroups_export_group_products", methods={"GET"})
		*/
	public function exportGroupProducts(Request $request): Response
	{
		$groupId = (int)$request->query->get('groupId');

		// Validate input
		if (!$groupId) {
			return new Response('Invalid group ID', 400);
		}

		$db = \Db::getInstance();

		try {
			// Fetch group products
			$sql = new \DbQuery();
			$sql->select('pl.id_product, pl.name, p.price');
			$sql->from('product_group_relationship', 'pgr');
			$sql->innerJoin('product_lang', 'pl', 'pgr.id_product = pl.id_product');
			$sql->innerJoin('product', 'p', 'pl.id_product = p.id_product');
			$sql->where('pgr.id_group = ' . (int)$groupId);
			$sql->where('pl.id_lang = ' . (int)\Context::getContext()->language->id);
      $sql->orderBy('pgr.product_order ASC');


			$products = $db->executeS($sql);

			// Create CSV content
			$csv = fopen('php://memory', 'w');
			fputcsv($csv, ['ID Produit', 'Nom du Produit', 'Prix']);

			foreach ($products as $product) {
				fputcsv($csv, [$product['id_product'], $product['name'], $product['price']]);
			}

			rewind($csv);
			$csvContent = stream_get_contents($csv);
			fclose($csv);

			// Create a response with the CSV content
			$response = new Response($csvContent);
			$response->headers->set('Content-Type', 'text/csv');
			$response->headers->set('Content-Disposition', 'attachment; filename="group_' . $groupId . '_products.csv"');

			return $response;
		} catch (\Exception $e) {
			// Log the error for debugging
			\PrestaShopLogger::addLog('Error exporting group products: ' . $e->getMessage(), 3);

			return new Response('Failed to export products: ' . $e->getMessage(), 500);
		}
	}
}
