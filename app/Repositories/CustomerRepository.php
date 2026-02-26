<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Customer;
use App\Models\User;
use App\Models\ShopUser;

class CustomerRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return Customer::class;
    }

    /**
     * Store customer by request.
     *
     * @param  User  $user  The user object
     */
    public static function storeByRequest(User $user): Customer
    {
        ShopUser::create([
            'user_id' => $user->id,
            'shop_id' => 1,
        ]);
        return self::create([
            'user_id' => $user->id,
        ]);
    }
}
