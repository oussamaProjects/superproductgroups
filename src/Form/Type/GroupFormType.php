<?php

namespace PrestaShop\Module\SuperProductGroups\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use PrestaShop\Module\SuperProductGroups\Form\Type\GroupEntryType;
use PrestaShop\Module\SuperProductGroups\Service\GroupService;

class GroupFormType extends AbstractType
{
    private $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $groups = $this->groupService->getFormData();

        $builder
            ->add(
                'groups',
                CollectionType::class,
                [
                    'entry_type' => GroupEntryType::class,
                    'entry_options' => [
                        'label' => "Group entry",
                        'groups' => $groups ,
                        'required' => true,
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => "Group",
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,                       // Set the data class if needed, or null for an array
            'groups' => null,                       // Set the data class if needed, or null for an array
        ]);

        $resolver->setAllowedTypes('groups', 'null');
    }

}
