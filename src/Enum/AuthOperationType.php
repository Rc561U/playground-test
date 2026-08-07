<?php

namespace App\Enum;

enum AuthOperationType: string
{
    case Login = 'login';
    case Register = 'register';
}
