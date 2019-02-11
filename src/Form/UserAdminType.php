<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\GenderEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('username')
            ->add('password')
            ->add('email')
            ->add('gender'
                , ChoiceType::class
                , ['choices' => GenderEnum::getAvailableTypes()])
            ->add('roles')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => User::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}