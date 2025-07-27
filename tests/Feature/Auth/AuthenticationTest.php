<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Disable CSRF middleware for authentication tests.
     * This is a common pattern for API and form submission testing.
     */
    private function withoutCsrfMiddleware(): self
    {
        return $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
        ]);
    }

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->withoutCsrfMiddleware()
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_with_remember_me()
    {
        $user = User::factory()->create();

        $response = $this->withoutCsrfMiddleware()
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
                'remember' => true,
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        
        // Verify remember token is set
        $this->assertNotNull(Auth::user()->getRememberToken());
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $response = $this->withoutCsrfMiddleware()
            ->from('/login')->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_not_authenticate_with_invalid_email()
    {
        $response = $this->withoutCsrfMiddleware()
            ->from('/login')->post('/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'password',
            ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_login_validation_requires_email_and_password()
    {
        $response = $this->withoutCsrfMiddleware()
            ->from('/login')->post('/login', []);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withoutCsrfMiddleware()
            ->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_logout_invalidates_session()
    {
        $user = User::factory()->create();
        
        // Login and get session ID
        $this->actingAs($user);
        $sessionId = session()->getId();
        
        // Logout
        $this->withoutCsrfMiddleware()
            ->post('/logout');

        $this->assertGuest();
        
        // Session should be invalidated (new session ID)
        $this->assertNotEquals($sessionId, session()->getId());
    }

    public function test_authenticated_users_cannot_view_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_unauthenticated_users_are_redirected_to_login()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
