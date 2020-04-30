<?php

namespace App\Form;

use App\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SubCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('name')
            ->add('extraFieldDefs'
                , CollectionType::class
                , [
                    'entry_type' => ExtraFieldDefType::class
                    //, 'entry_options' => ['label' => false]
                    , 'allow_add' => true
                    , 'allow_delete' => true
                    , 'by_reference' => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => SubCategory::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}
