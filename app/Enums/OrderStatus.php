<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'Pending';
    case CONFIRM = 'Confirm';
    case PROCESSING = 'Processing';
    case ON_THE_WAY = 'On The Way';
    case DELIVEREDTOCO = 'Delivered To Shipping Company';
    case ON_THE_WAY_TO_YOU = 'On The Way To You';
    case DELIVERED = 'Delivered';
    case CANCELLED = 'Cancelled';
}
