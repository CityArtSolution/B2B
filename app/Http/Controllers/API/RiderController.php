<?php
namespace App\Http\Controllers\API;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use App\Repositories\DriverRepository;
use App\Repositories\UserRepository;

class RiderController extends Controller
{
public function index()
{
    $status = 'approved';

    $riders = User::role(Roles::DRIVER->value)
        ->when($status, function ($query) use ($status) {
            $status = $status == 'approved';
            return $query->where('is_active', $status);
        })
        ->select('id', 'name')
        ->get()
        ->map(function ($user) {
            return [
                'id'   => $user->id,
                'name' => $user->name,
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $riders
    ]);
}

}
