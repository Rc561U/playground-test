<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequestDto
{
    #[Assert\NotBlank(message: 'Email обязателен.')]
    #[Assert\Email(message: 'Некорректный email.')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Пароль обязателен.')]
    #[Assert\Length(min: 6, minMessage: 'Пароль должен содержать минимум {{ limit }} символов.')]
    public ?string $password = null;
}
