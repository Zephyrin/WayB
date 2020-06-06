<?php

namespace App\Form;

use App\Entity\IntoBackpack;
use App\Entity\Have;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class IntoBackpackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('count')
            ->add('equipment'
                , EntityType::class
                , [
                    'class' => Have::class
                    , 'required' => false
                    , 'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->orderBy('u.ownQuantity', 'ASC');
                    }
                ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => IntoBackpack::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}