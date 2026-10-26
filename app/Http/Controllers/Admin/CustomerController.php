<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\ShopPasswordResetRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Repositories\AddressRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $search = trim((string) $request->search);
        $inactiveSince = now()->subDays(60);

        $base = User::role(Roles::CUSTOMER->value);

        $customers = (clone $base)->latest('id')
            ->with(['media', 'customer' => fn ($query) => $query->withCount('orders')
                ->withSum('orders as total_spent', 'payable_amount')
                ->withMax('orders as last_order_at', 'created_at')])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('Commercial_register', 'like', "%{$search}%");
            }))
            // new: no order yet, regular: 3 orders or more, inactive: ordered before but not in 60 days
            ->when($request->segment === 'new', fn ($q) => $q->whereDoesntHave('customer.orders'))
            ->when($request->segment === 'regular', fn ($q) => $q->whereHas('customer.orders', null, '>=', 3))
            ->when($request->segment === 'inactive', fn ($q) => $q->whereHas('customer.orders')
                ->whereDoesntHave('customer.orders', fn ($o) => $o->where('created_at', '>=', $inactiveSince)))
            ->paginate(20)->withQueryString();

        $segmentCounts = [
            'all' => (clone $base)->count(),
            'new' => (clone $base)->whereDoesntHave('customer.orders')->count(),
            'regular' => (clone $base)->whereHas('customer.orders', null, '>=', 3)->count(),
            'inactive' => (clone $base)->whereHas('customer.orders')
                ->whereDoesntHave('customer.orders', fn ($o) => $o->where('created_at', '>=', $inactiveSince))->count(),
        ];

        return view('admin.customer.index', compact('customers', 'segmentCounts', 'inactiveSince'));
    }

    public function create()
    {
        return view('admin.customer.create');
    }

    public function store(RegistrationRequest $request)
    {
        // Create a new user
        $user = UserRepository::registerNewUser($request);

        // Create a new customer
        $customer = CustomerRepository::storeByRequest($user);

        // create wallet
        WalletRepository::storeByRequest($user);
        
        // create address
        AddressRepository::create([
            'name'          => $request->name,
            'phone'         => $request->phone,
            'customer_id'   => $customer->id,
            'area'          => $request->address['area'] ?? null,
            'neighborhood'          => $request->address['neighborhood'] ?? null,
            'flat_no'       => $request->address['flat_no'] ?? null,
            'post_code'     => $request->address['Postal'] ?? null,
            'address_line'  => $request->address['address_line'] ?? null,
            'address_line2' => $request->address['address_line2'] ?? null,
            'is_default'    => true,
        ]);


        $user->assignRole(Roles::CUSTOMER->value);

        return to_route('admin.customer.index')->withSuccess(__('Created successfully'));
    }

    public function edit(User $user)
    {
        return view('admin.customer.edit', compact('user'));
    }

    public function update(User $user, UserRequest $request)
    {
        UserRepository::updateByRequest($request, $user);
        return to_route('admin.customer.index')->withSuccess(__('Updated successfully'));
    }

    public function destroy(User $user)
    {
        $media = $user->media;

        if ($media && Storage::exists($media->src)) {
            Storage::delete($media->src);
        }

        $user->wallet()?->delete();
        $user->syncPermissions([]);
        $user->syncRoles([]);

        $delTime = now()->format('YmdHis');

        $user->update([
            'phone' => $user->phone.'_deleted:'.$delTime,
            'email' => $user->email.'_deleted:'.$delTime,
            'deleted_at' => now(),
        ]);

        $media?->delete();

        return back()->withSuccess(__('Deleted successfully'));
    }

    public function resetPassword(User $user, ShopPasswordResetRequest $request)
    {
        // Update the user password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->withSuccess(__('Password updated successfully'));
    }
}
