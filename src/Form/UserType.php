<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            // You probably don’t want to edit roles from this form yet, so omit “roles”

            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,      // 👈 DO NOT map directly to the entity
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 6),
                ],
            ])
        ;

        $builder->add('roles', ChoiceType::class, [
            'choices' => [
                'User' => 'ROLE_USER',
            ],
            'multiple' => true, // <-- Set this to true
            'expanded' => true, // <-- Optional: Renders as checkboxes if true, a select dropdown if false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
