<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithoutCsrfMiddleware;

class SettingsRouteProtectionTest extends TestCase
{
    use RefreshDatabase, WithoutCsrfMiddleware;

    // Profile Routes Protection Tests

    public function test_guests_cannot_access_profile_settings()
    {
        $response = $this->get('/settings/profile');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_users_can_access_profile_settings()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/settings/profile');
        
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/profile'));
    }

    public function test_guests_cannot_update_profile()
    {
        $response = $this->withoutCsrfMiddleware()
            ->patch('/settings/profile', [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);
        
        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_delete_profile()
    {
        $response = $this->withoutCsrfMiddleware()
            ->delete('/settings/profile');
        
        $response->assertRedirect('/login');
    }

    // Password Routes Protection Tests

    public function test_guests_cannot_access_password_settings()
    {
        $response = $this->get('/settings/password');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_users_can_access_password_settings()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/settings/password');
        
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/password'));
    }

    public function test_guests_cannot_update_password()
    {
        $response = $this->withoutCsrfMiddleware()
            ->put('/settings/password', [
                'current_password' => 'password',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ]);
        
        $response->assertRedirect('/login');
    }

    // Appearance Routes Protection Tests

    public function test_guests_cannot_access_appearance_settings()
    {
        $response = $this->get('/settings/appearance');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_users_can_access_appearance_settings()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/settings/appearance');
        
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/appearance'));
    }

    // Settings Redirect Protection Tests

    public function test_guests_cannot_access_settings_redirect()
    {
        $response = $this->get('/settings');
        
        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_are_redirected_to_profile_from_settings()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/settings');
        
        $response->assertRedirect('/settings/profile');
    }

    // Email Verification Requirements Tests

    public function test_unverified_users_can_still_access_settings()
    {
        // Settings routes only require 'auth' middleware, not 'verified'
        $user = User::factory()->unverified()->create();
        
        $response = $this->actingAs($user)->get('/settings/profile');
        
        $response->assertOk();
    }

    public function test_unverified_users_can_access_password_settings()
    {
        $user = User::factory()->unverified()->create();
        
        $response = $this->actingAs($user)->get('/settings/password');
        
        $response->assertOk();
    }

    public function test_unverified_users_can_access_appearance_settings()
    {
        $user = User::factory()->unverified()->create();
        
        $response = $this->actingAs($user)->get('/settings/appearance');
        
        $response->assertOk();
    }

    // Session Management Tests

    public function test_settings_routes_handle_expired_session()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Verify settings is accessible
        $this->get('/settings/profile')->assertOk();
        
        // Simulate session expiration
        auth()->logout();
        
        // Verify settings is no longer accessible
        $this->get('/settings/profile')->assertRedirect('/login');
        $this->get('/settings/password')->assertRedirect('/login');
        $this->get('/settings/appearance')->assertRedirect('/login');
    }

    public function test_settings_routes_redirect_after_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Verify settings routes are accessible
        $this->get('/settings/profile')->assertOk();
        $this->get('/settings/password')->assertOk();
        $this->get('/settings/appearance')->assertOk();
        
        // Logout
        $this->withoutCsrfMiddleware()
            ->post('/logout')
            ->assertRedirect('/');
        
        // Verify settings routes are no longer accessible
        $this->get('/settings/profile')->assertRedirect('/login');
        $this->get('/settings/password')->assertRedirect('/login');
        $this->get('/settings/appearance')->assertRedirect('/login');
    }

    // Comprehensive Route Protection Test

    public function test_all_settings_routes_require_authentication()
    {
        // Test GET routes (should redirect to login)
        $getRoutes = [
            '/settings',
            '/settings/profile',
            '/settings/password',
            '/settings/appearance',
        ];

        foreach ($getRoutes as $route) {
            $response = $this->get($route);
            
            $this->assertEquals(
                302,
                $response->getStatusCode(),
                "Route GET {$route} should redirect unauthenticated users"
            );
            
            $this->assertTrue(
                str_ends_with($response->headers->get('Location'), '/login'),
                "Route GET {$route} should redirect to /login"
            );
        }

        // Test POST/PATCH/PUT/DELETE routes with CSRF disabled (should redirect to login)
        $modifyRoutes = [
            ['PATCH', '/settings/profile'],
            ['DELETE', '/settings/profile'],
            ['PUT', '/settings/password'],
        ];

        foreach ($modifyRoutes as [$method, $route]) {
            $response = $this->withoutCsrfMiddleware()->call($method, $route);
            
            $this->assertEquals(
                302,
                $response->getStatusCode(),
                "Route {$method} {$route} should redirect unauthenticated users"
            );
            
            $this->assertTrue(
                str_ends_with($response->headers->get('Location'), '/login'),
                "Route {$method} {$route} should redirect to /login"
            );
        }
    }

    // Middleware Order and Security Tests

    public function test_password_update_has_throttle_middleware()
    {
        $user = User::factory()->create();
        
        // Make multiple rapid password update attempts
        for ($i = 0; $i < 7; $i++) {
            $response = $this->actingAs($user)
                ->withoutCsrfMiddleware()
                ->put('/settings/password', [
                    'current_password' => 'wrongpassword',
                    'password' => 'newpassword',
                    'password_confirmation' => 'newpassword',
                ]);
        }
        
        // Should be throttled after 6 attempts
        $response->assertStatus(429); // Too Many Requests
    }

    public function test_csrf_protection_on_settings_forms()
    {
        $user = User::factory()->create();
        
        // Test PATCH profile without CSRF (should fail with CSRF enabled)
        $response = $this->actingAs($user)
            ->patch('/settings/profile', [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);
        
        $response->assertStatus(419); // CSRF token mismatch
        
        // Test PUT password without CSRF (should fail with CSRF enabled)
        $response = $this->actingAs($user)
            ->put('/settings/password', [
                'current_password' => 'password',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }
} 