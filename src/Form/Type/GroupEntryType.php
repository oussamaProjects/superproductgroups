<?php

namespace PrestaShop\Module\SuperProductGroups\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Product;

class GroupEntryType extends AbstractType
{


	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('group_id', HiddenType::class, [
				'required' => false, // This can be null for new groups
			])
			->add('group_name', TextType::class, [
				'label' => 'Group Name',
				'required' => true,
			])
			->add('group_order', hiddenType::class, [
				'label' => 'Group Order',
					'attr' => [
					'class' => 'js-group-order', // Add Select2 for better UX
				],
			])
			->add('group_image', FileType::class, [
				'label' => 'Group Image',
				'required' => false,
				'mapped' => false,
				'attr' => [
					'accept' => 'image/*', // Limit file types to images
				],
			])
			->add('group_products', ChoiceType::class, [
				'label' => 'Select Products',
				'choices' => $this->getProductChoices(),
				'multiple' => true,
				'expanded' => false,
				'required' => false,
				'attr' => [
					'class' => 'js-product-search', // Add Select2 for better UX
				],
			]);
	}

	private function getProductChoices(): array
	{
		$languageId = (int) \Context::getContext()->language->id;

		// Fetch products using PrestaShop ObjectModel
		$products = Product::getProducts($languageId, 0, 0, 'name', 'asc');

		// Prepare choices array
		$choices = [];
		foreach ($products as $product) {
			$choices[$product['name']] = $product['id_product'];
		}

		return $choices;
	}


	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => null, // Set the data class if needed, or null for an array
			'groups' => null, // Set the data class if needed, or null for an array
		]);

		$resolver->setAllowedTypes('groups', 'array');
	}
}
