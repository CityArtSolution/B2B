<?php

namespace App\Enums;

enum OrderStatus2: string
{
    case PENDING = 'Pending';
    case CONFIRM = 'Confirm';
    case PROCESSING = 'Processing';
    case ON_THE_WAY_TO_YOU = 'On The Way To You';
    case DELIVERED = 'Delivered';
    case CANCELLED = 'Cancelled';
}
