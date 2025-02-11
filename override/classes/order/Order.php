<?php

class Order extends OrderCore
{
    public function getProducts($products = false, $selected_products = false, $selected_qty = false, $fullInfos = true)
    {
        if (!$products) {
            $products = parent::getProductsDetail();
        }

        if (!$fullInfos) {
            return $products;
        }

        $result_array = [];
        foreach ($products as $row) {
            $row['super_product_id'] = 0;
            $row['super_product_name'] = '';
            $row['super_product_quantity'] = 0;

            // Fetch Super Product Data
            $sql = new DbQuery();
            $sql->select('spcf.id_super_product, pl.name AS super_product_name, spcf.quantity AS super_product_quantity');
            $sql->from('superproduct_cart_custom_fields', 'spcf');
            $sql->leftJoin('product_lang', 'pl', 'spcf.id_super_product = pl.id_product AND pl.id_lang = ' . (int) Context::getContext()->language->id);
            $sql->where('spcf.id_cart = ' . (int) $this->id_cart);
            $sql->where('spcf.id_product = ' . (int) $row['product_id']);

            $superProduct = Db::getInstance()->getRow($sql);

            if ($superProduct) {
                $row['super_product_id'] = $superProduct['id_super_product'];
                $row['super_product_name'] = $superProduct['super_product_name'];
                $row['super_product_quantity'] = $superProduct['super_product_quantity'];
            }

            // Store the updated product row
            $result_array[(int) $row['id_order_detail']] = $row;
        }

        return $result_array;
    }
}
