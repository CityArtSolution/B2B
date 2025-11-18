<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\OrderStatus2;
use App\Enums\PaymentStatus;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\GeneraleSetting;
use App\Models\Setting;
use App\Models\Order;
use App\Models\User;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Services\NotificationServices;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a order list with filter status.
     */
    public function index(Request $request, $status = null , $co)
    {
        $status = $status ? str_replace('_', ' ', $status) : '';
    
        $generaleSetting = GeneraleSetting::first();
        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = User::role(Roles::ROOT->value)->first()?->shop;
        }
        // last code
        // $orders = OrderRepository::query()
        //     ->when($shop, function ($query) use ($shop) {
        //         return $query->where('shop_id', $shop->id);
        //     })
        //     ->when($status !== null && $status !== '0' && $status !== 0, function ($query) use ($status) {
        //         return $query->where('order_status', $status);
        //     })
        //     ->when(isset($co), function ($query) use ($co) {
        //         return $query->where('is_company', 1);
        //     })->latest('id')->paginate(20);

        $query = OrderRepository::query();

        $query->where('is_company', $co);

        if ($shop) {
            $query->where('shop_id', $shop->id);
        }
        
        if (!empty($status) && $status !== '0' && $status !== 0) {
            $query->where('order_status', $status);
        }
        
        
        $orders = $query->latest('id')->paginate(20);

        return view('admin.order.index', compact('orders', 'status' , 'co'));
    }

    /**
     * Display the order details.
     */
    public function show(Order $order)
    {
        $orderStatus = OrderStatus::cases();
        $orderStatus2 = OrderStatus2::cases();
        
        $shop_methods = Setting::get('shop_methods');

        $riders = Driver::whereHas('user', function ($query) {
            return $query->where('is_active', true);
        })->get();

        return view('admin.order.show', compact('order', 'orderStatus' , 'orderStatus2', 'riders' , 'shop_methods'));
    }

    /**
     * Update the order status.
     */
    public function statusChange(Order $order, Request $request , $method)
    {
        $request->validate(['status' => 'required']);

        $order->update(['order_status' => $request->status , 'is_company' => $method]);

        $title = 'Order status updated';
        $message = 'Your order status updated to '.$request->status;
        $deviceKeys = $order->customer->user->devices->pluck('key')->toArray();

        if ($request->status == OrderStatus::CANCELLED->value) {
            foreach ($order->products as $product) {

                $qty = $product->pivot->quantity;

                $product->update(['quantity' => $product->quantity + $qty]);

                $flashSale = $product->flashSales?->first();
                $flashSaleProduct = null;

                if ($flashSale) {
                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();

                    if ($flashSaleProduct && $product->pivot?->price) {
                        if ($flashSaleProduct->pivot->sale_quantity >= $qty && ($product->pivot?->price == $flashSaleProduct->pivot->price)) {
                            $flashSale->products()->updateExistingPivot($product->id, [
                                'sale_quantity' => $flashSaleProduct->pivot->sale_quantity - $qty,
                            ]);
                        }
                    }
                }
            }
        }

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer->user_id,
            'type' => 'order',
        ];

        NotificationRepository::storeByRequest($notify);

        return back()->with('success', __('Order status updated successfully.'));
    }

    /**
     * Update the payment status.
     */
    public function paymentStatusToggle(Order $order)
    {
        if ($order->payment_status->value == PaymentStatus::PAID->value) {
            return back()->with('error', __('When order is paid, payment status cannot be changed.'));
        }
        $order->update(['payment_status' => PaymentStatus::PAID->value]);

        $title = 'Payment status updated';
        $message = __('Your payment status updated to paid. order code: ').$order->prefix.$order->order_code;
        $deviceKeys = $order->customer->user->devices->pluck('key')->toArray();

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer->user_id,
            'type' => 'order',
        ];

        NotificationRepository::storeByRequest($notify);

        return back()->with('success', __('Payment status updated successfully'));
    }
    /**
     * Display a order list with filter status.
     */
    public function updateStatus(Request $request)
    {
    $newStatus = $request->d_status;

    Setting::set('shop_methods',$newStatus);
    return redirect()->back()->with('success', __('Shipping methods updated successfully'));
    }

    public function uploadInvoice(Request $request, Order $order)
    {
        $request->validate([
            'delivered_invoice' => 'required|file|mimes:pdf,jpg,jpeg,png',
        ]);
    
        if ($request->hasFile('delivered_invoice')) {
            $file = $request->file('delivered_invoice');
    
            if ($order->delivered_invoice) {
                Storage::disk('public')->delete($order->delivered_invoice);
            }
    
            $path = $file->store('invoices', 'public');
            $order->delivered_invoice = $path; // تحديث الصف الحالي فقط
            $order->save();
        }
    
        return redirect()->back()->with('success', __('Upload Invoice'));
    }


    
}
