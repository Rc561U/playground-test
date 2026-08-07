<?php

namespace App\Enum;

enum AuthStatus: string
{
    case Success = 'success';
    case Error = 'error';
}
