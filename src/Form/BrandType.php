<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\MediaObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class BrandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('name')
            ->add('uri')
            ->add('validate')
            ->add('askValidate')
            ->add('logo'
                , EntityType::class
                , [
                    'class' => MediaObject::class
                    , 'required' => false
                    , 'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->orderBy('u.description', 'ASC');
                    }
                ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => Brand::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}
