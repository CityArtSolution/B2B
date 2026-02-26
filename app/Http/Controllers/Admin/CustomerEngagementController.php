<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserProductView;
use App\Enums\Roles;
use App\Models\User;

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
   
    public function account_statement($id)
    {
        $user = User::with(['customer.orders','customer.addresses','customer.returnOrders'])->role(Roles::CUSTOMER->value)->where('id' , $id)->first();
        return view('admin.CustomerEngagement.account-statement' , compact('user'));
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
