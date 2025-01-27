<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

use PrestaShop\Module\SuperProductGroups\Form\Type\GroupFormType;
use PrestaShop\Module\SuperProductGroups\Install\Installer;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;


if (!defined('_PS_VERSION_')) {
  exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class SuperProductGroups extends Module
{
  public function __construct()
  {
    $this->name = 'superproductgroups';
    $this->version = '1.0.0';
    $this->author = 'Your Name';
    $this->tab = 'administration';
    $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('Super Product Groups');
    $this->description = $this->l('Allows managing groups of products and displaying them in the front office.');
  }

  public function install()
  {
    if (!parent::install()) {
      return false;
    }

    $installer = new Installer();
    return $installer->install($this);
  }

  public function uninstall()
  {
    if (!parent::uninstall()) {
      return false;
    }

    $installer = new Installer();
    return $installer->uninstall($this);
  }

  public function hookActionAdminControllerSetMedia()
  {
    $this->context->controller->addCSS('https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
    $this->context->controller->addCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    $this->context->controller->addCSS('https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css');

    // Add your custom JS and CSS files
    $this->context->controller->addJS($this->_path . 'views/js/admin/superproductgroups.js');
    $this->context->controller->addCSS($this->_path . 'views/css/admin/superproductgroups.css');
  }

  public function hookActionFrontControllerSetMedia()
  {

    $this->context->controller->addJS('https://code.jquery.com/ui/1.13.2/jquery-ui.min.js');
    // Add your custom JS and CSS files
    $this->context->controller->addJS($this->_path . 'views/js/front/superproductgroups.js');
    $this->context->controller->addCSS($this->_path . 'views/css/front/superproductgroups.css');
  }

  public function hookDisplayAdminProductsExtra(array $params): string
  {

    $productId = $params['id_product'];
    $super_product = new Product($productId);

    // Retrieve groups for this product
    $formData = $this->getFormData($productId);

    // Create the form
    $formFactory = $this->get('form.factory');
    $form = $formFactory->create(GroupFormType::class, $formData);

    /** @var EngineInterface $twig */
    $twig = $this->get('twig');

    return $twig->render('@Modules/superproductgroups/views/templates/admin/group_form.html.twig', [
      'super_product' => $super_product,
      'form' => $form->createView(), // Pass the form view to the template
    ]);
  }


  public function getFormData($productId): array
  {
    // Get groups with products from the existing method
    $groupsWithProducts = $this->getThisProductGroupsWithProducts($productId);

    // Initialize the $formData array
    $formData = ['groups' => []];

    // Transform data to match the desired structure
    foreach ($groupsWithProducts as $group) {
      $formData['groups'][] = [
        'group_id' => $group['id'], // Include group ID
        'group_name' => $group['name'],
        'group_order' => $group['group_order'],
        'group_image' => $group['image'] ?? null, // Use the image path or null
        'group_products' => array_column($group['products'], 'id'), // Extract product IDs
      ];
    }

    return $formData;
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
    $sql->leftJoin(
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

    $sql->orderBy('pgr.id_group ASC');

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

  public function getThisProductGroupsWithProducts(int $productId)
  {
    $sql = new \DbQuery();
    $sql->select('
          pg.id_group AS id_group,
          pg.name AS group_name,
          pg.image AS group_image,
          pg.group_order AS group_order,
          p.id_product AS product_id,
          pl.name AS product_name,
          p.reference AS reference,
          ps.price AS product_price,
          pi.id_image AS product_image_id,
          pl.link_rewrite AS link_rewrite,
          stock.quantity AS stock_quantity
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
      'p.id_product = ps.id_product AND ps.id_shop = ' . (int)\Shop::getContextShopID()
    );

    // Join product_lang for product names in the current language
    $sql->leftJoin(
      'product_lang',
      'pl',
      'p.id_product = pl.id_product
              AND pl.id_lang = ' . (int)\Context::getContext()->language->getId() . '
              AND pl.id_shop = ' . (int)\Shop::getContextShopID()
    );

    // Join image table for image IDs
    $sql->leftJoin(
      'image',
      'pi',
      'p.id_product = pi.id_product AND pi.cover = 1' // Ensure the cover image is fetched
    );


    // Join stock_available to fetch stock quantity
    $sql->leftJoin(
      'stock_available',
      'stock',
      'p.id_product = stock.id_product AND stock.id_shop = ' . (int)\Shop::getContextShopID()
    );

    $sql->where('pg.id_super_product = ' . (int)$productId);
    $sql->orderBy('pg.group_order ASC');

    $result = \Db::getInstance()->executeS($sql);

    if (!$result) {
      return [];
    }

    // Initialize Link object to generate image URLs
    $link = new \Link();

    // Process results into a structured format
    $groups = [];
    // echo "<pre>";
    // print_r($result);
    // die;
    foreach ($result as $row) {
      if (!isset($groups[$row['id_group']])) {
        $groups[$row['id_group']] = [
          'id' => $row['id_group'],
          'name' => $row['group_name'],
          'image' => $row['group_image'],
          'group_order' => $row['group_order'],
          'products' => [],
        ];
      }

      if (!empty($row['product_id'])) {
        $imageUrl = null;

        // Generate the full product image URL
        if (!empty($row['product_image_id'])) {
          $imageUrl = "http://" . $link->getImageLink(
            $row['link_rewrite'], // SEO-friendly URL
            $row['product_id'] . '-' . $row['product_image_id'],
            'small_default'
          );
        }

        $groups[$row['id_group']]['products'][] = [
          'id' => $row['product_id'],
          'id_group' => $row['id_group'],
          'group_name' => $row['group_name'],
          'reference' => $row['reference'],
          'group_image' => $row['group_image'],
          'name' => $row['product_name'],
          'price' => number_format((float)$row['product_price'], 2, '.', ''), // Format price to 2 decimals
          'stock_quantity' => (int)$row['stock_quantity'], // Stock quantity
          'image' => $imageUrl,
        ];
      }
    }

    return $groups;
  }

  /**
   * Modify product form builder
   *
   * @param array $params
   */
  public function hookActionProductFormBuilderModifier(array $params): void
  {
    $formBuilder = $params['form_builder'];
    $formBuilder->add('the_group_tab', GroupFormType::class, [
      'label' => 'Product Groups',
      'required' => false,
    ]);
  }

  public function hookDisplayProductAdditionalInfo($params)
  {
    $logged = $this->context->customer->isLogged();
    // Retrieve product ID from parameters
    $productId = (int)$params['product']['id_product'];
    // Fetch groups associated with this product
    $groups = $this->getThisProductGroupsWithProducts($productId);
    // Assign data to the template
    $this->context->smarty->assign([
      'product' => $params['product'],
      'groups' => $groups,
      'logged' => $logged,
      'ajax_url' => $this->context->link->getModuleLink($this->name, 'ajax', []), // AJAX endpoint for fetching group details
    ]);

    return $this->display(__FILE__, 'views/templates/front/group_list.tpl');
  }

  public function hookActionCartSave($params)
  {
    if (!isset($this->context->cart) || !$this->context->cart->id) {
      return;
    }

    $cart = $this->context->cart;
    $rawCustomFields = Tools::getValue('custom_fields');

    // Decode the custom fields
    $customFields = $rawCustomFields ? json_decode($rawCustomFields, true) : null;
    // Get the current product ID
    $currentProductId = Tools::getValue('id_product');

    // Fetch the quantity of the current product from the cart
    $productQuantity = 1; // Default to 1
    if ($currentProductId) {
      $productsInCart = $cart->getProducts();
      foreach ($productsInCart as $product) {
        if ((int)$product['id_product'] === (int)$currentProductId) {
          $productQuantity = (int)$product['cart_quantity'];
          break;
        }
      }
    }

    // Initialize customFields if empty or missing main_product_id
    if (empty($customFields) || empty($customFields['main_product_id'])) {

      $customFields = [
        'main_product_id' => 0, // Default value for main_product_id
        // 'quantity' => $productQuantity,
        'quantity' => 1,
        'is_associated' => false, // Default value indicating no association
      ];

      error_log('Custom Fields initialized with default data: ' . print_r($customFields, true));
    }
    // else{
    //   $customFields['quantity'] = $productQuantity;
    // }

    // If a product ID is provided, remove duplicates for the same main_product_id
    if ($currentProductId) {
      // $this->removeProductsFromCartByProductAndMainProduct($cart->id, $currentProductId, $customFields['main_product_id']);
      // $this->deleteCustomFieldsByProductAndMainProduct($cart->id, $currentProductId, $customFields['main_product_id']);
    }

    // Save the custom fields for the current product
    $currentProductId = Tools::getValue('id_product');
    if ($currentProductId) {
      $this->saveCustomFieldsToCart($cart->id, $currentProductId, $customFields);
    }
  }

  private function removeProductsFromCartByProductAndMainProduct($cartId, $productId, $mainProductId)
  {
    // Delete the specific product with the given main_product_id
    Db::getInstance()->delete(
      'cart_product',
      'id_cart = ' . (int)$cartId . '
         AND id_product = ' . (int)$productId . '
         AND id_product IN (
            SELECT id_product
            FROM ' . _DB_PREFIX_ . 'cart_custom_fields
            WHERE id_cart = ' . (int)$cartId . '
              AND JSON_UNQUOTE(JSON_EXTRACT(custom_fields, "$.main_product_id")) = ' . (int)$mainProductId . '
         )'
    );
  }

  private function deleteCustomFieldsByProductAndMainProduct($cartId, $productId, $mainProductId)
  {
    // Delete the custom fields for the specific product with the given main_product_id
    Db::getInstance()->delete(
      'cart_custom_fields',
      'id_cart = ' . (int)$cartId . '
         AND id_product = ' . (int)$productId . '
         AND JSON_UNQUOTE(JSON_EXTRACT(custom_fields, "$.main_product_id")) = ' . (int)$mainProductId
    );
  }

  private function saveCustomFieldsToCart($cartId, $productId, $customFields)
  {
    // Insert or update custom fields for the specific product
    Db::getInstance()->insert('cart_custom_fields', [
      'id_cart' => (int) $cartId,
      'id_product' => (int) $productId,
      'custom_fields' => pSQL(json_encode($customFields)),
      'date_add' => date('Y-m-d H:i:s'),
      'date_upd' => date('Y-m-d H:i:s'),
    ]);
  }

  public function hookDisplayShoppingCart($params)
  {
    $cartId = $this->context->cart->id;
    $languageId = (int)$this->context->language->id;

    // Fetch the cart custom fields
    $customFields = $this->getCartCustomFields($cartId, $languageId);

    // Format data for easy use in the template
    $customFieldsByProduct = $this->formatCustomFieldsData($customFields);

    // Assign data to Smarty
    $this->context->smarty->assign([
      'customFieldsByProduct' => $customFieldsByProduct,
    ]);

    // Return the custom template
    return $this->display(__FILE__, 'views/templates/front/cart_super_products.tpl');
  }

  /**
   * Fetch custom fields for the cart, including product and super product names.
   */
  private function getCartCustomFields($cartId, $languageId)
  {
    $query = '
        SELECT
            ccf.id_product,
            pl.name AS product_name,
            COALESCE(pl_super.name, "Unassociated") AS super_product_name,
            JSON_UNQUOTE(JSON_EXTRACT(ccf.custom_fields, "$.main_product_id")) AS main_product_id,
            SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(ccf.custom_fields, "$.quantity")) AS UNSIGNED)) AS total_quantity
        FROM ' . _DB_PREFIX_ . 'cart_custom_fields ccf
        INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl
            ON ccf.id_product = pl.id_product
        LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl_super
            ON JSON_UNQUOTE(JSON_EXTRACT(ccf.custom_fields, "$.main_product_id")) = pl_super.id_product
            AND pl_super.id_lang = ' . (int)$languageId . '
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_product cp
            ON ccf.id_product = cp.id_product AND cp.id_cart = ccf.id_cart
        WHERE ccf.id_cart = ' . (int)$cartId . '
          AND pl.id_lang = ' . (int)$languageId . ' 
        GROUP BY ccf.id_product, main_product_id
    ';

    return Db::getInstance()->executeS($query);
  }

  /**
   * Format custom fields for easier use in templates.
   */
  private function formatCustomFieldsData($customFields)
  {
    $formattedData = [];
    foreach ($customFields as $field) {
      $formattedData[] = [
        'product_name' => $field['product_name'],
        'super_product_name' => $field['super_product_name'],
        'main_product_id' => $field['main_product_id'],
        'total_quantity' => $field['total_quantity'],
      ];
    }
    return $formattedData;
  }

  public function hookActionValidateOrder($params)
  {
    $order = $params['order'];
    $cartId = $order->id_cart;

    // Update cart records to use the final order ID
    Db::getInstance()->update(
      'superproduct_order',
      ['id_order' => (int) $order->id],
      'id_order = ' . (int) $cartId
    );
  }

  public function hookDisplayAdminOrderMain($params)
  {
    $orderId = (int) $params['id_order'];
    $languageId = (int)$this->context->language->id;

    // Fetch the cartId associated with the order
    $cartId = Db::getInstance()->getValue(
      'SELECT id_cart
           FROM ' . _DB_PREFIX_ . 'orders
           WHERE id_order = ' . $orderId
    );

    if (!$cartId) {
      // If no cart ID is found, stop execution
      return '<p>No cart associated with this order.</p>';
    }

    // Fetch custom fields, super product names, and product names for the cart
    $customFields = Db::getInstance()->executeS(
      '
          SELECT
              ccf.id_product,
              pl.name AS product_name,
              pl_super.name AS super_product_name,
              JSON_UNQUOTE(JSON_EXTRACT(ccf.custom_fields, "$.main_product_id")) AS main_product_id
          FROM ' . _DB_PREFIX_ . 'cart_custom_fields ccf
          INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl
              ON ccf.id_product = pl.id_product
          LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl_super
              ON JSON_UNQUOTE(JSON_EXTRACT(ccf.custom_fields, "$.main_product_id")) = pl_super.id_product
          INNER JOIN ' . _DB_PREFIX_ . 'cart_product cp
              ON ccf.id_product = cp.id_product AND cp.id_cart = ccf.id_cart
          WHERE ccf.id_cart = ' . (int)$cartId . '
            AND pl.id_lang = ' . (int)$languageId . '
            AND pl_super.id_lang = ' . (int)$languageId . '
          GROUP BY ccf.id_product, main_product_id
          '
    );

    // Format data for easy access in the template
    $products = [];
    foreach ($customFields as $field) {
      $products[] = [
        'product_name' => $field['product_name'], // Product name
        'super_product_name' => $field['super_product_name'], // Super product name
      ];
    }

    // Assign data to Smarty
    $this->context->smarty->assign([
      'orderProducts' => $products,
    ]);

    // Display the custom template
    return $this->display(__FILE__, 'views/templates/admin/order_products_groups.tpl');
  }
}
