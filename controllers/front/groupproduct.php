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
    $productId = (int) Tools::getValue('id_product');

    // Validate input
    if (!is_array($selectedProducts) || !$productId) {
      die(json_encode(['status' => 'error', 'message' => 'Invalid data format or missing product ID.']));
    }

    // Start or resume the session
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Initialize session data if it doesn't exist
    if (!isset($_SESSION['selected_products'])) {
      $_SESSION['selected_products'] = [];
    }

    // Update or set the selected products for the specific product ID
    $_SESSION['selected_products'][$productId] = $selectedProducts;

    die(json_encode(['status' => 'success', 'message' => 'Products saved successfully.']));
  }

  private function getSelectedProducts()
  {
    $productId = (int) Tools::getValue('id_product');

    if (!$productId) {
      die(json_encode(['status' => 'error', 'message' => 'Missing product ID.']));
    }

    // Start or resume the session
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Retrieve data from session
    $allProducts = $_SESSION['selected_products'] ?? [];

    // Fetch selected products for the given product ID
    $selectedProducts = $allProducts[$productId] ?? [];

    die(json_encode(['status' => 'success', 'selectedProducts' => $selectedProducts]));
  }

  private function clearSelectedProducts()
  {
    $productId = (int) Tools::getValue('id_product');

    if (!$productId) {
      die(json_encode(['status' => 'error', 'message' => 'Missing product ID.']));
    }

    // Start or resume the session
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Remove selected products for the given product ID
    if (isset($_SESSION['selected_products'][$productId])) {
      unset($_SESSION['selected_products'][$productId]);
    }

    die(json_encode(['status' => 'success', 'message' => 'Selected products cleared successfully.']));
  }
}
