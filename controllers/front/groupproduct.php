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
    $superProductId = (int) Tools::getValue('id_super_product');

    if (!$superProductId) {
      die(json_encode(['status' => 'error', 'message' => 'No product ID provided.']));
    }
    $groups = $this->module->getThisProductGroupsWithProducts($superProductId);

    if (!empty($groups)) {
      die(json_encode(['status' => 'success', 'hasGroups' => true]));
    }

    die(json_encode(['status' => 'success', 'hasGroups' => false]));
  }
  private function saveSelectedProducts()
  {
    $selectedProducts = Tools::getValue('selectedProducts');
    $superProductId = (int) Tools::getValue('id_super_product');

    // Validate input
    if (!$superProductId) {
      die(json_encode(['status' => 'error', 'message' => 'Missing product ID.']));
    }

    if (!is_array($selectedProducts)) {
      $selectedProducts = [];
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
    $_SESSION['selected_products'][$superProductId] = $selectedProducts;

    die(json_encode(['status' => 'success', 'message' => 'Products saved successfully.']));
  }

  private function getSelectedProducts()
  {
    $superProductId = (int) Tools::getValue('id_super_product');

    if (!$superProductId) {
      die(json_encode(['status' => 'error', 'message' => 'Missing product ID.']));
    }

    // Start or resume the session
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Retrieve data from session
    $allProducts = $_SESSION['selected_products'] ?? [];

    // Fetch selected products for the given product ID
    $selectedProducts = $allProducts[$superProductId] ?? [];

    die(json_encode(['status' => 'success', 'selectedProducts' => $selectedProducts]));
  }

  private function clearSelectedProducts()
  {
    $superProductId = (int) Tools::getValue('id_super_product');

    if (!$superProductId) {
      die(json_encode(['status' => 'error', 'message' => 'Missing product ID.']));
    }

    // Start or resume the session
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    // Remove selected products for the given product ID
    if (isset($_SESSION['selected_products'][$superProductId])) {
      unset($_SESSION['selected_products'][$superProductId]);
    }

    die(json_encode(['status' => 'success', 'message' => 'Selected products cleared successfully.']));
  }
}
