<?php

namespace App\Form;

use App\Entity\ExtraFieldDef;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
                , EntityType::class
                , [
                    'class' => ExtraFieldDef::class
                    , 'required' => false
                    , 'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    }
                 ]
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
