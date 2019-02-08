<?php

namespace App\Form;

use App\Entity\Have;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HaveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ownQuantity')
            ->add('wantQuantity')
            ->add('equipment')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Have::class,
            'allow_extra_fields' => true,
            'csrf_protection'    => false,
        ]);
    }
}
