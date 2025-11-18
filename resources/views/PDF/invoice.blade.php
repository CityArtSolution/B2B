@php
    $directory = app()->getLocale() == 'ar' ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&amp;display=swap" rel="stylesheet">
    <title>Invoice</title>
    <style>
        body {
            position: relative;
            color: #303042;
            font-family: "Almarai", sans-serif;
            background-color: #F9FAFC;
            font-size: 16px;
            font-weight: 400;
            margin: 0;
            padding: 16px;
        }

        p,h2,h1,h3,h4,h5,h6 {
            margin: 0;
        }

        .header {
            width: 100%;
            color: #5E6470;
            padding: 12px;
        }

        .header .row {
            width: 50%;
        }

        .header .logo {
            width: 90px;
            height: 90px;
        }

        .header img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .text-right {
            text-align: right !important;
        }

        .pl-3 {
            padding-left: 12px;
        }

        .pt-2 {
            padding: 5px;
        }

        .pt-1-5 {
            padding-top: 2px;
        }

        .pt-1 {
            padding-top: 4px;
        }

        .pt-3 {
            padding-top: 12px;
        }

        .site-name {
            font-size: 18px;
            font-weight: 600;
            color: #303042;
            line-height: normal;
        }

        .text-gray {
            color: #5E6470;
        }

        .fz-14 {
            font-size: 14px;
            line-height: 16px
        }

        .contains {
            position: absolute;
            padding: 12px;
            background: #fff;
            left: 16px;
            right: 55px;
            bottom: 16px;
            top: 120px;
            border-radius: 16px;
        }

        .fw-400 {
            font-weight: 400 !important;
        }

        .fw-500 {
            font-weight: 500;
        }

        .w-full {
            width: 100%;
        }

        .payAmount {
            font-size: 20px;
            font-style: normal;
            font-weight: 700;
            line-height: 28px;
        }

        .qrCode {
            width: 61px;
            height: 60px;
        }

        .invoice-details {
            width: 100% !important;
            margin-top: 40px;
            margin-left: 30px
        }

        .invoice-details tr th {
            color: #5E6470;
        }

        .items-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .items-table tr th {
            padding: 12px;
            background: #3546AE;
            color: #fff;
            font-style: normal;
        }

        .items-table tr {
            border-right: 0.5px solid #CFCFCF;
            border-bottom: 0.5px solid #CFCFCF;
            border-left: 0.5px solid #CFCFCF;
            border-top: 0;
        }

        .items-table tr td {
            padding: 12px;
            background: #FFF;
        }

        .text-center {
            text-align: center !important;
        }

        .product-des {
            font-size: 10px;
            font-weight: 400;
        }

        .invoice-total {
            width: 320px;
            float: {{ $directory == 'rtl' ? 'left' : 'right' }};
            margin-top: 8px;
        }

        .border-top {
            border-top: 1px solid #CFCFCF;
            margin-top: 6px;
        }

        .total {
            font-size: 16px;
            font-weight: 700;
        }

        .footer {
            width: 90%;
            position: absolute;
            left: 32px;
            right: 0;
            bottom: 16px;
            color: #303042;
            padding: 8px;
        }

        .footer .signature {
            border: 1px solid #303042;
            background-clip: border-box;
            padding: 0 8px;
        }

        .float-left {
            float: left !important;
        }

        .float-right {
            float: right !important;
        }

        .pt-4 {
            padding-top: 20px;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .w-50 {
            width: 50%;
        }

        .text {
            color: #5E6470;
        }

        .address span {
            color: #5E6470;
            font-size: 13px;
        }

        .address_name {
            color: #5E6470;
            font-size: 12px;
        }
    </style>
    @if ($directory == 'rtl')
        <style>
            body {
                direction: rtl !important;
            }

            .items-table tr td {
                font-weight: normal !important;
            }

            .items-table tr th {
                font-weight: normal !important;
            }

            .items-table tr th.text-left {
                text-align: right !important;
            }

            .invoice-details tr th {
                text-align: right;
                font-weight: normal !important;
            }

            .invoice-details tr td {
                font-weight: normal !important;
            }
        </style>
    @else
        <style>
            body {
                direction: ltr !important;
            }

            .items-table tr td {
                font-weight: 500;
            }

            .items-table tr th {
                font-weight: 600;
            }
        </style>
    @endif
</head>

<body>
    <div style="padding: 14px;">
    <div style="width: 100%; margin-bottom: 20px; border: 3px solid gray; border-radius: 9px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: center;">
            <tr>
                <td style="width: 35%; text-align: right; vertical-align: top; font-size: 10px; padding: 5px;line-height: 2;">
                    <strong>شركة سحاب روائع الندى للتجارة</strong><br>
                    الرياض المرقب شارع الرس جوال 0533349224<br>
                    جدة البلد برج الاحذية الدور 12 جوال 0533358228<br>
                    س.ت : 1010701821
                </td>
    
                <td style="width: 30%; text-align: center; vertical-align: middle; padding: 5px;">
                    <img src="{{ $generaleSetting?->favicon ?? asset('assets/favicon.png') }}" alt="logo" style="width: 90px; height: 90px; object-fit: contain;">
                </td>
    
                <td style="width: 35%; text-align: left; vertical-align: top; font-size: 10px; padding: 5px;line-height: 2;">
                    <strong>SAHAB RAWA AL NADA TRADING COMPANY</strong><br>
                     RIYADH AL MURQAB RASS STREET TEL: 0533349224<br>
                     JADDAH AL BALAD BORJ AL AHTAIA-TEL: 0533358228<br>
                    C.R: 1010701821
                </td>
            </tr>
        </table>
    </div>
  </div>

    <div class="contains">
        @php
            $address = $order->address;
            $user = $order->customer?->user;
        @endphp

        <table style="width:100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
            <tr>
                <td style="width:33%; padding:4px;color:#0d0da5;">{{ __('Date') }}: <b style="color: darkred;">{{ now()->format('d-m-Y') }}</b></td>
                <td style="width:33%; padding:4px;color:darkblue;"><h1>{{ __('Tax invoice') }}</h1></td>
                <td style="width:33%; padding:4px;color:#0d0da5;">{{ __('Invoice Number') }}: <b style="color: darkred;">#{{ $order->prefix . $order->order_code }}</b></td>
            </tr>
        </table>

        <table style="width:100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
            <tr>
                <td style="width:33%; padding:4px;font-weight:bold">المخرج:</b></td>
                <td style="width:33%; padding:4px;">{{ __('The client\'s commercial register') }} : <b>{{ $user->Commercial_register ?? '' }}</b></td>
                <td style="width:33%; padding:4px;">{{ __('Customer\'s mobile number') }}: <b>{{ $user?->phone }}</b></td>
            </tr>
        </table>
    
        <table style="width:100%; border-collapse: collapse; direction: rtl;">
            <tr>
                <!-- جدول بيانات المورد -->
                <td style="width:50%; vertical-align: top; padding-right:5px;">
                    <table style="width:100%; border-collapse: collapse; font-size: 14px;">
                
                        <tr>
                            <td colspan="2" style="text-align:center; font-weight:bold; padding:6px; background:#e8e8e8; border:1px solid #000;">
                                {{ __('supplier data') }}
                            </td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('name') }}</td>
                            <td style="border:1px solid #000; padding:6px;"><strong>شركة سحاب روائع الندى للتجارة</strong></td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('building number') }}</td>
                            <td style="border:1px solid #000; padding:6px;">6394</td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('street name') }}</td>
                            <td style="border:1px solid #000; padding:6px;"> ﻋﻴﻦ ﺍﻟﺤﻮﺍﺱ</td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('district') }}</td>
                            <td style="border:1px solid #000; padding:6px;"> ﺣﻲ ﺍﻟﻤﺮﻗﺐ</td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('city') }}</td>
                            <td style="border:1px solid #000; padding:6px;">الرياض</td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('country') }}</td>
                            <td style="border:1px solid #000; padding:6px;">المملكة العربية السعودية</td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('postal code') }}</td>
                            <td style="border:1px solid #000; padding:6px;">12645</td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('additional number') }}</td>
                            <td style="border:1px solid #000; padding:6px;">2611</td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('tax number') }}</td>
                            <td style="border:1px solid #000; padding:6px;">311304046200003</td>
                        </tr>
                
                    </table>
                </td>

                <!-- جدول بيانات العميل -->
                <td style="width:50%; vertical-align: top; padding-left:5px;">
                    <table style="width:100%; border-collapse: collapse; font-size: 14px;">
                        <tr>
                            <td colspan="2"
                                style="text-align:center; font-weight:bold; padding:6px; background:#e8e8e8; border:1px solid #000;">
                                {{ __('client data') }}
                            </td>
                        </tr>
                
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client name') }}</td><td style="border:1px solid #000; padding:6px;">{{ $user->name }}</td>
                        </tr>
                        
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client building_number') }}</td><td style="border:1px solid #000; padding:6px;">{{ $address->flat_no }}</td>
                        </tr>
                        
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client street_name') }}</td><td style="border:1px solid #000; padding:6px;">{{ $address->area }}</td>
                        </tr>
                        
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client district') }}</td><td style="border:1px solid #000; padding:6px;"></td>
                        </tr>
                        
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client city') }}</td><td style="border:1px solid #000; padding:6px;">{{ $address->address_line }}</td>
                        </tr>{{ $address->area }}
                        
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client country') }}</td><td style="border:1px solid #000; padding:6px;">{{ $address->address_line2 }}</td>
                        </tr>
                        
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client postal_code') }}</td><td style="border:1px solid #000; padding:6px;">{{ $address->post_code }}</td>
                        </tr>
                        
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client additional_number') }}</td><td style="border:1px solid #000; padding:6px;"></td>
                        </tr>
                        
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">{{ __('client tax_number') }}</td><td style="border:1px solid #000; padding:6px;"></td>
                        </tr>
                    </table>
                </td>

            </tr>
        </table>

        <div class="clearfix w-full">

            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-left">
                            م
                        </th>
                        <th class="text-left">
                            {{ __('Item Number') }}
                        </th>
                        <th class="text-left">
                            {{ __('Description') }}
                        </th>
                        <th class="text-center">
                            {{ __('unit') }}
                        </th>
                        <th class="text-center">
                            {{ __('Size') }}
                        </th>
                        <th class="text-center">
                            {{ __('Qty') }}
                        </th>
                        <th class="text-right">
                            {{ __('Price') }}
                        </th>
                        <th class="text-right">
                            {{ __('Total') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->products ?? [] as $product)
                        @php
                            $price = $product->discount_price > 0 ? $product->discount_price : $product->price;

                            $name = $product->name;
                            $shortDescription = $product->short_description;

                            if ($directory == 'rtl') {
                                $translation = $product->translations()?->where('lang', 'ar')->first();
                                $name = $translation?->name ?? $name;
                                $shortDescription = $translation?->short_description ?? $short_description;
                            }
                            $plainShortDescription = strip_tags($shortDescription);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $product->id}}</td>
                            <td style="border: none !important">
                                <table>
                                    <tr>
                                        <td style="width: 40px !important; padding: 0 !important">
                                            <img src="{{ $product->thumbnail }}" alt=""
                                                style="width: 40px; height: 40px">
                                        </td>
                                        <td style="padding: 3px">
                                            <span style="text-transform: capitalize">
                                                {{ $name }}
                                            </span>
                                            <p class="pt-1 text-gray product-des">
                                                {{ $plainShortDescription }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="text-center">{{ $product->unit->name }}</td>
                            <td class="text-center">{{ $product->pivot->size ?? '--' }}</td>
                            <td class="text-center">{{ $product->pivot->quantity }}</td>
                            <td class="text-center fw-400">{{ showCurrency($price) }}</td>
                            <td class="text-right">{{ showCurrency($price * $product->pivot->quantity) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($directory != 'rtl')
            <div class="invoice-total">
                <div class="pt-2 w-full">
                    <p class="float-left w-50">
                        {{ __('Sub Total') }}
                    </p>
                    <p class="w-50 text-right fw-500">
                        {{ showCurrency($order->total_amount) }}
                    </p>
                </div>
                @if ($order->coupon_discount > 0)
                    <div class="w-full pt-2">
                        <p class="w-50 float-left">
                            {{ __('Discount') }}
                        </p>
                        <p class="w-50 text-right fw-500">
                            {{ showCurrency($order->coupon_discount) }}
                        </p>
                    </div>
                @endif
                <div class="w-full pt-2">
                    <p class="w-50 float-left">
                        {{ __('Delivery Charge') }}
                    </p>
                    <p class="w-50 text-right fw-500">
                        {{ showCurrency($order->delivery_charge) }}
                    </p>
                </div>

                @foreach ($order->vatTaxes ?? [] as $vatTax)
                    <div class="w-full pt-2">
                        <p class="w-50 float-left">
                            {{ $vatTax->name . '(' . $vatTax->percentage . '%)' }}
                        </p>
                        <p class="w-50 text-right fw-500">
                            {{ showCurrency($vatTax->amount) }}
                        </p>
                    </div>
                @endforeach
                @if ($order->tax_amount > 0 && count($order->vatTaxes ?? []) <= 0)
                    <div class="w-full pt-2">
                        <p class="w-50 float-left">
                            {{ __('Total Tax Amount') }}
                        </p>
                        <p class="w-50 text-right fw-500">
                            {{ showCurrency($order->tax_amount) }}
                        </p>
                    </div>
                @endif
                <div class="w-full pt-2 border-top">
                    <p class="w-50 float-left">
                        {{ __('Total Amount') }}
                    </p>
                    <p class="w-50 text-right total">
                        {{ showCurrency($order->payable_amount) }}
                    </p>
                </div>
            </div>
        @else
            <table style="width:100%; border-collapse: collapse; font-size: 14px;">                
                <tr>
                    <td style="border:1px solid #000; padding:6px;"> ﺍﻻﺟﻤﺎﻟﻲ ﻗﺒﻞ ﺿﺮﻳﺒﺔ ﺍﻟﻘﻴﻤﺔ ﺍﻟﻤﻀﺎﻓﺔ</td>
                    <td style="border:1px solid #000; padding:6px;text-align: left;">{{ showCurrency($order->total_amount) }}</td>
                </tr>
                @if ($order->coupon_discount > 0)
                <tr>
                    <td style="border:1px solid #000; padding:6px;">{{ __('Discount') }}</td>
                    <td style="border:1px solid #000; padding:6px;text-align: left;">{{ showCurrency($order->coupon_discount) }}</td>
                </tr>
                @endif
                <tr>
                    <td style="border:1px solid #000; padding:6px;">{{ __('Delivery Charge') }}</td>
                    <td style="border:1px solid #000; padding:6px;text-align: left;">{{ showCurrency($order->delivery_charge) }}</td>
                </tr>
                @if ($order->tax_amount > 0)
                <tr>
                    <td style="border:1px solid #000; padding:6px;">ضريبة القيمة المضافة 15%</td>
                    <td style="border:1px solid #000; padding:6px;text-align: left;"> {{ showCurrency($order->tax_amount) }}</td>
                </tr>
                @endif
                <tr>
                    <td style="border:1px solid #000; padding:6px;">{{ __('Total Amount') }}</td>
                    <td style="border:1px solid #000; padding:6px;text-align: left;">{{ showCurrency($order->payable_amount) }}</td>
                </tr>
            </table>
        @endif
    </div>

</body>

</html>
