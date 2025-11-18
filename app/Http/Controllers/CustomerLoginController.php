<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\CustomerRepository;
use App\Repositories\DeviceKeyRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class CustomerLoginController extends Controller
{
    public function index(){
        return view('customer.auth.login');
    }
    
    /**
     * Login a user.
     *
     * @param  LoginRequest  $request  The login request data
     */
    public function login(LoginRequest $request)
    {
        // Authenticate the user
        $user = $this->authenticate($request);
        
        if ($user?->customer) {
            auth()->login($user);
            return redirect('/')->with('message', 'تم تسجيل الدخول بنجاح');

        }

        return redirect()->back()->with('messages' , 'succ');
    }

    /**
     * Authenticate the user and return the user.
     *
     * @param  LoginRequest  $request  The login request
     * @return User|null
     */
    private function authenticate(LoginRequest $request)
    {
        $user = UserRepository::findByPhone($request->phone);
        if (! is_null($user) && Hash::check($request->password, $user->password)) {
            return $user;
        }

        return null;
    }

    /**
     * Logout the user and revoke the token.
     *
     * @model User $user
     *
     * @return string
     */
    public function logout()
    {
        /** @var \User $user */
        $user = auth()->user();

        if ($user) {
            $user->currentAccessToken()->delete();

            return $this->json('Logged out successfully!');
        }

        return $this->json('User not found!', [], Response::HTTP_NOT_FOUND);
    }

}
