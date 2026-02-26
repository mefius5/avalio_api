<?php

namespace App\Enums;

enum OrderStatus: string
{
    case CREATED = 'CREATED';
    case CONFIRMED = 'CONFIRMED';
}

