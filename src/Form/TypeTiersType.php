<?php

namespace App\Form;

use App\Entity\TypeTiers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TypeTiersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prefixe', TextType::class, [
                'label' => 'Préfixe',
                'label_attr' => ['class' => 'lab30 obligatoire'],
                'attr' => [
                    'class' => 'form-control w70',
                    'autocomplete' => 'off'
                ]
            ])
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'label_attr' => ['class' => 'lab30 obligatoire'],
                'attr' => [
                    'class' => 'form-control w70', 
                    'autocomplete' => 'off'
                ]
            ])
            ->add('numeroInitial', TextType::class, [
                'label' => 'N° Initial',
                'label_attr' => ['class' => 'lab30'],
                'attr' => [
                    'class' => 'form-control w70',
                    'autocomplete' => 'off'
                ],
                'required' => false
            ])
            ->add('format', TextType::class, [
                'label' => 'Format',
                'label_attr' => ['class' => 'lab30 obligatoire'],
                'attr' => [
                    'class' => 'form-control w70'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeTiers::class,
        ]);
    }
}
