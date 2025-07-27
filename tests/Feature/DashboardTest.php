<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\Traits\WithoutCsrfMiddleware;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithoutCsrfMiddleware;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('dashboard'));
    }

    public function test_unverified_users_can_access_dashboard_without_verification()
    {
        // Note: Email verification is not currently enabled (MustVerifyEmail not implemented)
        $user = User::factory()->unverified()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('dashboard'));
    }

    public function test_dashboard_middleware_configuration()
    {
        // Test with unauthenticated user
        $this->get('/dashboard')->assertRedirect('/login');
        
        // Test with authenticated user (email verification not enforced currently)
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
        
        // Test with unverified user (should still work since MustVerifyEmail not implemented)
        $unverifiedUser = User::factory()->unverified()->create();
        $this->actingAs($unverifiedUser)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_dashboard_handles_expired_session()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Simulate session expiration by clearing auth
        Auth::logout();
        
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_dashboard_redirects_after_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Verify dashboard is accessible
        $this->get('/dashboard')->assertOk();
        
        // Logout
        $this->withoutCsrfMiddleware()
            ->post('/logout')
            ->assertRedirect('/');
        
        // Verify dashboard is no longer accessible
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_preserves_intended_url_after_login()
    {
        // Attempt to access dashboard as guest
        $this->get('/dashboard')->assertRedirect('/login');
        
        // Login and verify redirect to intended URL
        $user = User::factory()->create();
        $this->withoutCsrfMiddleware()
            ->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect('/dashboard');
    }
}
