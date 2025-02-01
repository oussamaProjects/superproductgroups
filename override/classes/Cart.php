<?php

class Cart extends CartCore
{
  protected $context;

  public function __construct($id = null)
  {
    parent::__construct($id);
    $this->context = Context::getContext();
  }

  public function getProducts($refresh = false, $id_product = false, $id_country = null, $fullInfos = true, $keepOrderPrices = false)
  {
    // Get standard cart products
    $products = parent::getProducts($refresh, $id_product, $id_country, $fullInfos);

    // Build the query using DbQuery
    $sql = new DbQuery();
    $sql->select('
            ccf.id_product,
            ccf.id_super_product AS super_product_id,
            pl.name AS super_product_name,
            ccf.quantity AS super_product_quantity,
            p.reference AS super_product_reference
        ');
    $sql->from('superproduct_cart_custom_fields', 'ccf');
    $sql->leftJoin('product', 'p', 'ccf.id_super_product = p.id_product');
    $sql->leftJoin('product_lang', 'pl', 'ccf.id_super_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id);
    $sql->where('ccf.id_cart = ' . (int) $this->id);

    // Execute the query
    $customFields = Db::getInstance()->executeS($sql);

    // Map custom fields by product ID
    $customFieldsByProduct = [];
    foreach ($customFields as $field) {
      $customFieldsByProduct[$field['id_product']] = [
        'super_product_id' => $field['super_product_id'] ?? 0,
        'super_product_name' => $field['super_product_name'] ?? 'N/A', // Default if not found
        'super_product_quantity' => $field['super_product_quantity'] ?? 1, // Default to 1 if missing
        'super_product_reference' => $field['super_product_reference'] ?? '', // Include product reference
      ];
    }

    // Attach `id_super_product`, `super_product_quantity`, `super_product_name`, and `super_product_reference` to cart products
    foreach ($products as &$product) {
      $productId = $product['id_product'];
      $product['super_product_id'] = $customFieldsByProduct[$productId]['super_product_id'] ?? 0;
      $product['super_product_name'] = $customFieldsByProduct[$productId]['super_product_name'] ?? 'N/A';
      $product['super_product_reference'] = $customFieldsByProduct[$productId]['super_product_reference'] ?? '';
      $product['super_product_quantity'] = $customFieldsByProduct[$productId]['super_product_quantity'] ?? $product['cart_quantity'];
    }

    return $products;
  }
}
