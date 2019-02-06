<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Equipment;
use App\Entity\SubCategory;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder,
                              array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('brand'
                , EntityType::class
                , [
                    'class' => Brand::class
                    , 'required' => false
                    , 'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    }
                ])
            ->add('subCategory'
                , EntityType::class
                , [
                    'class' => SubCategory::class
                    , 'required' => false
                    , 'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    }
                ])
            ->add('extraFields'
                , CollectionType::class
                , [
                    'entry_type' => ExtraFieldType::class
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
                'data_class'         => Equipment::class,
                'allow_extra_fields' => true,
                'csrf_protection'    => false,
            ]
        );
    }
}
