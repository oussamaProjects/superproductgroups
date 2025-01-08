<?php

class SuperproductgroupsGroupproductModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        // Get the requested action
        $action = Tools::getValue('action');

        // Dispatch based on the action
        switch ($action) {
            case 'SaveSelectedProducts':
                $this->saveSelectedProducts();
                break;

            case 'GetSelectedProducts':
                $this->getSelectedProducts();
                break;

            default:
                die(json_encode(['status' => 'error', 'message' => 'Invalid action.']));
        }
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
}
