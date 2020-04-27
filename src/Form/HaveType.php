<?php

namespace App\Form;

use App\Entity\Have;
use App\Entity\Equipment;
use App\Form\EquipmentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HaveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ownQuantity')
            ->add('wantQuantity')
            ->add('equipment'
                , EntityType::class
                , [
                    'class' => Equipment::class
                    , 'required' => false
                    , 'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    }
                ])
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
