<?php

namespace App\Form;

use App\Entity\ExtraFieldDef;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtraFieldDefType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('type')
            ->add('name')
            ->add('isPrice')
            ->add('isWeight')
            ->add('linkTo'
//                , ExtraFieldDefType::class,
//                [ 'required' => false
////                    , ''
////                    , 'property_path' => 'id'
//                ]
                )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => ExtraFieldDef::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false
            ]
        );
    }
}
