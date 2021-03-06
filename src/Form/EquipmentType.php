<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Equipment;
use App\Entity\SubCategory;
use App\Repository\SubCategoryRepository;
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
            ->add('linkToManufacturer')
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
                    , 'required' => true
                    , 'query_builder' => function(SubCategoryRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    }
                ])
            ->add('characteristics'
                , CollectionType::class
                , [
                    'entry_type' => CharacteristicType::class
                    , 'allow_add' => true
                    , 'allow_delete' => true
                    , 'by_reference' => false
                ]
            )
            ->add('validate')
            ->add('askValidate')
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
