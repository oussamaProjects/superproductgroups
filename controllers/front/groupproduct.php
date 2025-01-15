<?php

class SuperproductgroupsGroupproductModuleFrontController extends ModuleFrontController
{
  public function postProcess()
  {
    // Get the requested action
    $action = Tools::getValue('action');

    // Dispatch based on the action
    switch ($action) {
      case 'CheckProductHasGroups':
        $this->checkProductHasGroups();
        break;

      case 'SaveSelectedProducts':
        $this->saveSelectedProducts();
        break;

      case 'GetSelectedProducts':
        $this->getSelectedProducts();
        break;

      case 'ClearSelectedProducts':
        $this->clearSelectedProducts();
        break;

      default:
        die(json_encode(['status' => 'error', 'message' => 'Invalid action.']));
    }
  }

  public function checkProductHasGroups()
{
    $productId = (int) Tools::getValue('id_product');

    if (!$productId) {
        die(json_encode(['status' => 'error', 'message' => 'No product ID provided.']));
    }
    $groups = $this->module->getThisProductGroupsWithProducts($productId);

    if (!empty($groups)) {
        die(json_encode(['status' => 'success', 'hasGroups' => true]));
    }

    die(json_encode(['status' => 'success', 'hasGroups' => false]));
}

  private function saveSelectedProducts()
  {
    $selectedProducts = Tools::getValue('selectedProducts');

    // Validate input
    if (!is_array($selectedProducts)) {
      die(json_encode(['status' => 'error', 'message' => 'Invalid data format.']));
    }

    // Save to cookie
    $this->context->cookie->__set('selected_products', json_encode($selectedProducts));
    die(json_encode(['status' => 'success', 'message' => 'Products saved successfully.']));
  }

  private function getSelectedProducts()
  {
    $selectedProducts = json_decode($this->context->cookie->__get('selected_products'), true) ?? [];
    die(json_encode(['status' => 'success', 'selectedProducts' => $selectedProducts]));
  }

  private function clearSelectedProducts()
  {
      // Clear the selected products from the cookie
      $this->context->cookie->__unset('selected_products');
      die(json_encode(['status' => 'success', 'message' => 'Selected products cleared successfully.']));
  }
}
