<?php

namespace App\Form;

use App\Entity\ExtraField;
use App\Enum\TypeExtraFieldEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ExtraFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('type'
                , ChoiceType::class
                , [ 'choices' => TypeExtraFieldEnum::getAvailableTypes()])
            ->add('name')
            ->add('isPrice')
            ->add('isWeight')
            ->add('value')
            ->add('referTo'
                , EntityType::class
                , [
                    'class' => ExtraFieldDef::class
                    , 'required' => true
                    , 'query_builder' => function(ExtraFieldDefRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    }
                ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => ExtraField::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}
