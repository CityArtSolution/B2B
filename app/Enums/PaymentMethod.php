<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case NEW_CLIENT = 'New client';
    case PREVIOUS_CLIENT = 'Previous client';
    case ONLINE = 'Online Payment';
    case STRIPE = 'Stripe';
    case PAYPAL = 'PayPal';
    case TAP = 'Tap';
    case TABBY = 'Tabby';
    case TAMARA = 'Tamara';
    case RAZORPAY = 'Razorpay';
    case PAYSTACK = 'PayStack';
    case AAMARPAY = 'Amarpay';
    case BKASH = 'Bkash';
    case PAYTABS = 'PayTabs';
}