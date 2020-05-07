<?php

namespace App\Form;

use App\Entity\Characteristic;
use App\Enum\GenderEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CharacteristicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('gender'
                , ChoiceType::class
                , [ 'choices' => GenderEnum::getAvailableTypes()])
            ->add('size')
            ->add('price')
            ->add('weight')
            ->add('validate')
            ->add('askValidate')
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => Characteristic::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}
