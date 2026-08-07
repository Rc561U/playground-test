<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'security.register.email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'user@example.com'],
                'constraints' => [
                    new Assert\NotBlank(message: 'validation.email.required'),
                    new Assert\Email(message: 'validation.email.invalid'),
                    new Assert\Length(max: 254),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'security.register.password',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
                    'constraints' => [
                        new Assert\NotBlank(message: 'validation.password.required'),
                        new Assert\Length(
                            min: 6,
                            max: 32,
                            minMessage: 'validation.password.min_length',
                            maxMessage: 'validation.password.max_length',
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => 'security.register.confirm_password',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'security.register.passwords_mismatch',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
