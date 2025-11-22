<?php

namespace App\Enums;

enum CustomerPaymentStatus: string
{
    case CASH = 'cash';
    case CREDIT = 'credit';

}
