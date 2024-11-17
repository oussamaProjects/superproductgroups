<?php

namespace PrestaShop\Module\SuperProductGroups\Service;

class GroupService
{
    public function getAvailableGroups(): array
    {
        $sql = new \DbQuery();
        $sql->select('id_group, name');
        $sql->from('product_group');

        $result = \Db::getInstance()->executeS($sql);

        if (!$result) {
            return [];
        }

        $groups = [];
        foreach ($result as $row) {
            $groups[$row['name']] = $row['id_group'];
        }

        return $groups;
    }

    public function getGroupsWithProducts(): array
    {
        $sql = new \DbQuery();
        $sql->select('
            pg.id_group AS id_group,
            pg.name AS group_name,
            pg.image AS group_image,
            p.id_product AS product_id,
            pl.name AS product_name,
            p.price AS product_price
        ');
        $sql->from('product_group', 'pg');

        // Join product_group_relationship to link groups with products
        $sql->leftJoin(
            'product_group_relationship',
            'pgr',
            'pg.id_group = pgr.id_group'
        );

        // Join product table for product details
        $sql->leftJoin(
            'product',
            'p',
            'pgr.id_product = p.id_product'
        );

        // Join product_shop for shop-specific details
        $sql->innerJoin(
            'product_shop',
            'ps',
            'p.id_product = ps.id_product AND ps.id_shop = ' . (int) \Shop::getContextShopID()
        );

        // Join product_lang for product names in the current language
        $sql->leftJoin(
            'product_lang',
            'pl',
            'p.id_product = pl.id_product
            AND pl.id_lang = ' . (int) \Context::getContext()->language->getId() . '
            AND pl.id_shop = ' . (int) \Shop::getContextShopID()
        );

        $result = \Db::getInstance()->executeS($sql);

        if (!$result) {
            return [];
        }

        // Process results into a structured format
        $groups = [];
        foreach ($result as $row) {
            if (!isset($groups[$row['id_group']])) {
                $groups[$row['id_group']] = [
                    'id' => $row['id_group'],
                    'name' => $row['group_name'],
                    'image' => $row['group_image'],
                    'products' => [],
                ];
            }

            if (!empty($row['product_id'])) {
                $groups[$row['id_group']]['products'][] = [
                    'id' => $row['product_id'],
                    'name' => $row['product_name'],
                    'price' => $row['product_price'],
                ];
            }
        }

        return $groups;
    }

    public function getFormData(): array
    {
        // Get groups with products from the existing method
        $groupsWithProducts = $this->getGroupsWithProducts();

        // Initialize the $formData array
        $formData = ['groups' => []];

        // Transform data to match the desired structure
        foreach ($groupsWithProducts as $group) {
            $formData['groups'][] = [
                'group_name' => $group['name'],
                'group_image' => $group['image'], // Use the image path or null
                'group_products' => array_column($group['products'], 'id'), // Extract product IDs
            ];
        }

        return $formData;
    }

    /**
     * Gets the associated language object for the current context.
     */
    private function getAssociatedLanguage()
    {
        return \Context::getContext()->language;
    }
}
