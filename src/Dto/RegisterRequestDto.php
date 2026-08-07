<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequestDto
{
    #[Assert\NotBlank(message: 'validation.email.required')]
    #[Assert\Email(message: 'validation.email.invalid')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'validation.password.required')]
    #[Assert\Length(min: 6, minMessage: 'validation.password.min_length')]
    public ?string $password = null;
}
