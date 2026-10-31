<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckHasRootUser;
use Tests\TestCase;

class AdminIntendedRedirectTest extends TestCase
{
    public function test_guest_admin_destination_is_saved_for_after_login(): void
    {
        $response = $this
            ->withoutMiddleware(CheckHasRootUser::class)
            ->get('/admin/payment-gateway');

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('url.intended', url('/admin/payment-gateway'));
    }
}
