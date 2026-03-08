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

        if ($co === 'courier') {
            $query->whereIn('shipping_type', ['courier', 'private']);
        } else {
            $query->where('shipping_type', $co);
        }

        if ($shop) {
            $query->where('shop_id', $shop->id);
        }
        
        if (!empty($status) && $status !== '0' && $status !== 0) {
            $query->where('order_status', $status);
        }
        
        
        $orders = $query->latest('id')->paginate(20);

        return view('admin.order.index', compact('orders', 'status' , 'co'));
    }


    public function accept(Order $order)
    {

        $order->update(['order_status' => 'Accepted']);
        foreach ($order->products as $product) {
            $quantityToSubtract = $product->pivot->quantity;
            $branchId = $product->pivot->branch_id;
            $branchProduct = $product->quantityBranch($branchId);
            if ($branchProduct) {

                $branchProduct->decrement('qty', $quantityToSubtract);
            }
        }
        return redirect()->back();
    }
    /**
     * Display the order details.
     */
    public function show(Order $order)
    {
        $orderStatus = OrderStatus::cases();
        $orderStatus2 = OrderStatus2::cases();
        
        Order::where('id', $order->id)->update([
            'is_read' => true,
        ]);

        $riders = Driver::whereHas('user', function ($query) {
            return $query->where('is_active', true);
        })->get();

        return view('admin.order.show', compact('order', 'orderStatus' , 'orderStatus2', 'riders'));
    }

    /**
     * Update the order status.
     */
    public function statusChange(Order $order, Request $request)
    {
        $request->validate(['status' => 'required']);

        $order->update(['order_status' => $request->status]);

        $title = 'Order status updated';
        $message = 'Your order status updated to '.$request->status;
        $deviceKeys = $order->customer->user->devices->pluck('key')->toArray();

        if ($request->status == OrderStatus::CANCELLED->value) {
            foreach ($order->products as $product) {

                $qty = $product->pivot->quantity;

                $product->update(['quantity' => $product->quantity + $qty]);

                $flashSale = $product->activeFlashSale($product->pivot->branch_id);

                if ($flashSale) {
                    if ($product->pivot?->price) {
                        if ($flashSale->pivot->sale_quantity >= $qty && ($product->pivot?->price == $flashSale->pivot->price)) {
                            $flashSale->products()->updateExistingPivot($product->id, [
                                'sale_quantity' => $flashSale->pivot->sale_quantity - $qty,
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
    public function updateDeliveryMethod(Request $request , $orderID)
    {
        $validated = $request->validate([
            'd_status' => 'required|in:company,private,courier',
        ]);
        
        $order = Order::findOrFail($orderID);
        $newStatus = $request->d_status;
    
        $order->shipping_type = $newStatus;
        $order->save();

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
