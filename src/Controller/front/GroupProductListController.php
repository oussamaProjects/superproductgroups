<?php

namespace PrestaShop\Module\SuperProductGroups\Controller\Front;

class GroupProductController extends ModuleFrontController
{
  public function initContent()
  {
    parent::initContent();

    $productId = (int) Tools::getValue('id_product');
    $product = new \SuperProductGroups\Product\ProductExtension($productId);
    $groups = $product->getGroups();

    $this->context->smarty->assign([
      'groups' => $groups,
    ]);

    $this->setTemplate('module:superproductgroups/views/templates/front/group-list.tpl');
  }


  public function ajaxProcessGetGroupProducts()
  {
    $groupId = (int)Tools::getValue('group_id');
    $search = Tools::getValue('search', '');

    $sql = new \DbQuery();
    $sql->select('p.id_product, pl.name');
    $sql->from('product', 'p');
    $sql->innerJoin('product_group_relationship', 'pgr', 'p.id_product = pgr.id_product');
    $sql->innerJoin('product_lang', 'pl', 'p.id_product = pl.id_product');
    $sql->where('pgr.id_group = ' . $groupId);
    $sql->where('pl.id_lang = ' . (int)$this->context->language->id);

    if (!empty($search)) {
      $sql->where('pl.name LIKE "%' . pSQL($search) . '%"');
    }

    $products = \Db::getInstance()->executeS($sql);

    die(json_encode(['products' => $products]));
  }

}
