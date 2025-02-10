<?php

declare(strict_types=1);

namespace PrestaShop\Module\SuperProductGroups\Form\Modifier;

use PrestaShopBundle\Form\FormBuilderModifier;
use Symfony\Component\Form\FormBuilderInterface;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;
use PrestaShop\Module\SuperProductGroups\Form\Type\GroupFormType;

final class ProductFormModifier
{
    /**
     * @var FormBuilderModifier
     */
    private $formBuilderModifier;

    /**
     * Constructor to initialize the FormBuilderModifier.
     *
     * @param FormBuilderModifier $formBuilderModifier
     */
    public function __construct(FormBuilderModifier $formBuilderModifier)
    {
        $this->formBuilderModifier = $formBuilderModifier;
    }

    // /**
    //  * Modifies the product form by adding a group selection field.
    //  *
    //  * @param int $productId The ID of the product being edited
    //  * @param FormBuilderInterface $productFormBuilder The form builder for the product
    //  */
    // public function modify(int $productId, FormBuilderInterface $productFormBuilder): void
    // {
    //     $this->addCustomTab($productId, $productFormBuilder);
    // }


    // /**
    //  * @param CustomProduct $customProduct
    //  * @param FormBuilderInterface $productFormBuilder
    //  */
    // private function addCustomTab(int $productId, FormBuilderInterface $productFormBuilder): void
    // {

    //     // Add a multiple-choice field to select groups (name, image) associated with the products in the form builder using the form builder modifier

    //     $this->formBuilderModifier->addAfter(
    //         $productFormBuilder,
    //         'description',
    //         'the_group_tab',
    //         GroupFormType::class,
    //         [
    //             'label' => 'Group Information',
    //         ]
    //     );
    // }
}
