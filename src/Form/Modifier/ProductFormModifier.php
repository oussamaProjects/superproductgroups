<?php

declare(strict_types=1);

namespace PrestaShop\Module\SuperProductGroups\Form\Modifier;

use PrestaShopBundle\Form\FormBuilderModifier;

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
}
