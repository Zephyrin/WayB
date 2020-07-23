<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CategoryType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ) {
        $builder
            ->add('name')
            ->add(
                'subCategories',
                CollectionType::class,
                [
                    'entry_type' => SubCategoryType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ]
            )
            ->add('validate')
            ->add('askValidate');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => Category::class,
                'allow_extra_fields' => false,
                'csrf_protection'    => false,
            ]
        );
    }
}
