<?php

namespace App\Form;

use App\Entity\Backpack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class BackpackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('name')
            ->add('intoBackpacks'
                , CollectionType::class
                , [
                    'entry_type' => IntoBackpackType::class
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
                'data_class'         => Backpack::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}