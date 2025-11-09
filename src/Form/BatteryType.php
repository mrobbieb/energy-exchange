<?php

namespace App\Form;

use App\Entity\Battery;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatteryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'placeholder' => 'Choose a User'
                
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('batteryBank')
            ->add('updatedAt', null, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Battery::class,
        ]);
    }
}
