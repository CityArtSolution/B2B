<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\OrderStatus2;
use App\Enums\PaymentStatus;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\Tabby\ProcessController as TabbyProcessController;
use App\Http\Controllers\Gateway\Tamara\ProcessController as TamaraProcessController;
use App\Models\Driver;
use App\Models\GeneraleSetting;
use App\Models\Setting;
use App\Models\Order;
use App\Models\User;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Services\NotificationServices;
use App\Services\OrderStockService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a order list with filter status.
     */
    public function index(Request $request, $status = null, $co = null)
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
        } elseif ($co) {
            $query->where('shipping_type', $co);
        }

        if ($shop) {
            $query->where('shop_id', $shop->id);
        }

        // search by order code (with or without its prefix), customer name or phone
        $search = trim((string) $request->search);
        $query->when($search !== '', function ($query) use ($search) {
            $code = preg_replace('/^\D+/', '', $search);
            $query->where(function ($query) use ($search, $code) {
                $query->when($code !== '', fn ($q) => $q->where('order_code', 'like', "%{$code}%"))
                    ->orWhereHas('customer.user', function ($user) use ($search) {
                        $user->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        })
            ->when($request->payment_status, fn ($q, $value) => $q->where('payment_status', $value))
            ->when($request->payment_method, fn ($q, $value) => $q->where('payment_method', $value))
            ->when($request->from, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($request->to, fn ($q, $value) => $q->whereDate('created_at', '<=', $value));

        // counts per status for the tabs, with the same filters
        $statusCounts = (clone $query)->reorder()->selectRaw('order_status, count(*) as total')
            ->groupBy('order_status')->pluck('total', 'order_status');

        if (!empty($status) && $status !== '0' && $status !== 0) {
            $query->where('order_status', $status);
        }

        $orders = $query->latest('id')->paginate(20)->withQueryString();
        $paymentMethods = \DB::table('orders')->whereNotNull('payment_method')->distinct()->pluck('payment_method');

        return view('admin.order.index', compact('orders', 'status', 'co', 'statusCounts', 'paymentMethods'));
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

        // Judge by the status before the change: an unpaid online order never took stock
        $stockTaken = OrderStockService::isTaken($order);

        $order->update(['order_status' => $request->status]);

        $title = 'Order status updated';
        $message = 'Your order status updated to '.$request->status;
        $deviceKeys = $order->customer?->user?->devices?->pluck('key')?->toArray() ?? [];

        if ($request->status == OrderStatus::CANCELLED->value && $stockTaken) {
            OrderStockService::release($order);
        }

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer?->user_id ?? $order->customer?->user?->id,
            'type' => 'order',
        ];

        NotificationRepository::storeByRequest($notify);

        return back()->with('success', __('Order status updated successfully.'));
    }

    /**
     * Update the payment status.
     */
    /**
     * Refund part or all of an order paid with Tabby or Tamara, through the gateway's API
     */
    public function gatewayRefund(Request $request, Order $order)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.round((float) $order->payable_amount, 2),
            'reason' => 'nullable|string|max:255',
        ]);

        $payment = $order->payments()->where('is_paid', true)->latest('id')->first();
        $gateway = strtolower((string) $payment?->payment_method);
        if (! in_array($gateway, ['tabby', 'tamara'], true)) {
            return back()->with('error', __('This order was not paid with Tabby or Tamara.'));
        }

        try {
            if ($gateway === 'tabby') {
                $result = TabbyProcessController::refund($payment, (float) $data['amount'], $data['reason'] ?? null);
                $refunded = collect($result['refunds'] ?? [])->sum(fn ($refund) => (float) ($refund['amount'] ?? 0));
            } else {
                $result = TamaraProcessController::refund($payment, (float) $data['amount'], $data['reason'] ?? null);
                $refunded = $result['refunded_total'];
            }
        } catch (\Throwable $th) {
            return back()->with('error', $th instanceof \RuntimeException ? $th->getMessage() : __('Unable to connect to the payment gateway. Please try again.'));
        }

        return back()->with('success', __('Refund sent. Total refunded on this payment: :amount', ['amount' => number_format($refunded, 2)]));
    }

    public function paymentStatusToggle(Order $order)
    {
        if ($order->payment_status->value == PaymentStatus::PAID->value) {
            return back()->with('error', __('When order is paid, payment status cannot be changed.'));
        }
        $order->update(['payment_status' => PaymentStatus::PAID->value]);

        $title = 'Payment status updated';
        $message = __('Your payment status updated to paid. order code: ').$order->prefix.$order->order_code;
        $deviceKeys = $order->customer?->user?->devices?->pluck('key')?->toArray() ?? [];

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer?->user_id ?? $order->customer?->user?->id,
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
