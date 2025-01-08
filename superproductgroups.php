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
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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
    $formData = $this->getFormData($productId );

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


  public function getFormData($productId ): array
  {
    // Get groups with products from the existing method
    $groupsWithProducts = $this->getThisProductGroupsWithProducts($productId );

    // Initialize the $formData array
    $formData = ['groups' => []];

    // Transform data to match the desired structure
    foreach ($groupsWithProducts as $group) {
      $formData['groups'][] = [
        'group_id' => $group['id'], // Include group ID
        'group_name' => $group['name'],
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

  private function getThisProductGroupsWithProducts(int $productId)
  {
    $sql = new \DbQuery();
    $sql->select('
          pg.id_group AS id_group,
          pg.name AS group_name,
          pg.image AS group_image,
          p.id_product AS product_id,
          pl.name AS product_name,
          ps.price AS product_price,
          pi.id_image AS product_image_id,
          pl.link_rewrite AS link_rewrite
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

    $sql->where('pg.id_super_product = ' . (int)$productId);

    $result = \Db::getInstance()->executeS($sql);

    if (!$result) {
      return [];
    }

    // Initialize Link object to generate image URLs
    $link = new \Link();

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
        $imageUrl = null;

        // Generate the full product image URL
        if (!empty($row['product_image_id'])) {
          $imageUrl =  "http://" .  $link->getImageLink(
            $row['link_rewrite'], // SEO-friendly URL
            $row['product_id'] . '-' . $row['product_image_id'],
            'small_default'
          );
        }

        $groups[$row['id_group']]['products'][] = [
          'id' => $row['product_id'],
          'id_group' => $row['id_group'],
          'group_name' => $row['group_name'],
          'group_image' => $row['group_image'],
          'name' => $row['product_name'],
          'price' => number_format((float)$row['product_price'], 2, '.', ''), // Format price to 2 decimals
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
}
