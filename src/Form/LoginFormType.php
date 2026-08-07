<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'security.login.email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'user@example.com'],
                'constraints' => [
                    new Assert\NotBlank(message: 'validation.email.required'),
                    new Assert\Email(message: 'validation.email.invalid'),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'security.login.password',
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'autocomplete' => 'current-password'],
                'constraints' => [
                    new Assert\NotBlank(message: 'validation.password.required'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
