<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserProductView;
use App\Enums\Roles;
use App\Models\User;
use App\Models\Order;
use App\Models\ReturnOrder;
use App\Models\GeneraleSetting;

class CustomerEngagementController extends Controller
{
    public function index()
    {
        $records = UserProductView::with(['user', 'product'])
            ->where('visit_count', '>=', 3)
            ->where('total_time', '>=', 30)
            ->orderByDesc('visit_count')
            ->get();

        return view('admin.CustomerEngagement.index', compact('records'));
    }

    public function newCustomers()
    {
        $users = User::role(Roles::CUSTOMER->value)->where('payment_status' , 'New_client')->get();
        
        foreach ($users as $user) {
            $user->paid_invoices_total = $user->customer ? $user->customer->orders()->with('payments')->get()->flatMap(fn($order) => $order->payments)->where('is_paid', 1)->sum('amount') : 0;
        
            $user->unpaid_invoices_total = $user->customer ? $user->customer->orders()->with('payments')->get()->flatMap(fn($order) => $order->payments)->where('is_paid', 0)->sum('amount'): 0;
        }

        return view('admin.CustomerEngagement.newCustomers' , compact('users'));
    }

    public function existingCustomers()
    {
        $users = User::role(Roles::CUSTOMER->value)->where('payment_status' , 'Previous_client')->get();
        
        foreach ($users as $user) {
            $user->paid_invoices_total = $user->customer ? $user->customer->orders()->with('payments')->get()->flatMap(fn($order) => $order->payments)->where('is_paid', 1)->sum('amount') : 0;
        
            $user->unpaid_invoices_total = $user->customer ? $user->customer->orders()->with('payments')->get()->flatMap(fn($order) => $order->payments)->where('is_paid', 0)->sum('amount'): 0;
        }
        
        return view('admin.CustomerEngagement.existingCustomers' ,  compact('users'));
    }
   
    public function account_statement(Request $request, $id)
    {
        $user = User::with(['customer.addresses'])
            ->role(Roles::CUSTOMER->value)
            ->findOrFail($id);

        $customer = $user->customer;

        $ordersQuery = $customer ? $customer->orders()->latest() : Order::whereRaw('1=0');
        $returnsQuery = $customer ? $customer->returnOrders()->latest() : ReturnOrder::whereRaw('1=0');

        // Filter by dates if provided
        if ($request->filled('from_date')) {
            $ordersQuery->whereDate('created_at', '>=', $request->from_date);
            $returnsQuery->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $ordersQuery->whereDate('created_at', '<=', $request->to_date);
            $returnsQuery->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by payment status if provided
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'Paid') {
                $ordersQuery->where('payment_status', 'Paid');
            } elseif (in_array($request->payment_status, ['Pending', 'Unpaid'])) {
                $ordersQuery->where('payment_status', '!=', 'Paid');
            }
        }

        $orders = $ordersQuery->get();
        $returnOrders = $returnsQuery->get();

        // Calculate financials accurately
        $totalInvoicedAmount = (float) $orders->sum('payable_amount');

        $totalPaidAmount = (float) $orders->filter(function ($order) {
            $status = is_object($order->payment_status) ? $order->payment_status->value : $order->payment_status;
            return strtolower((string) $status) === 'paid';
        })->sum('payable_amount');

        $totalUnpaidAmount = (float) $orders->filter(function ($order) {
            $status = is_object($order->payment_status) ? $order->payment_status->value : $order->payment_status;
            return strtolower((string) $status) !== 'paid';
        })->sum('payable_amount');

        $totalReturnsAmount = (float) $returnOrders->sum('amount');

        // Net balance due (المتبقي المستحق = غير المدفوع - المرتجعات)
        $balanceDue = max(0, $totalUnpaidAmount - $totalReturnsAmount);

        $generaleSetting = GeneraleSetting::first();

        return view('admin.CustomerEngagement.account-statement', compact(
            'user',
            'orders',
            'returnOrders',
            'totalInvoicedAmount',
            'totalPaidAmount',
            'totalUnpaidAmount',
            'totalReturnsAmount',
            'balanceDue',
            'generaleSetting'
        ));
    }

    public function update_limit(Request $request , $userId)
    {
        
        $request->validate([
            'maximum_invoices_total' => ['required', 'numeric', 'min:0'],
        ]);
        
        $users = User::findOrFail($userId);
        
        $users->maximum_invoices_total = $request->maximum_invoices_total;
        
        $users->save();
        
        return redirect()->back()->with([
            'success' => __('Credit limit updated successfully'),
        ]);
    }
}
