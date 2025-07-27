<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        $response = $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_creates_user_in_database()
    {
        $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Verify password is hashed, not stored in plain text
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotEquals('password', $user->password);
        $this->assertTrue(\Hash::check('password', $user->password));
    }

    public function test_name_is_required()
    {
        $response = $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => '',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    public function test_email_is_required()
    {
        $response = $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => '',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_email_must_be_valid_format()
    {
        $response = $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_email_must_be_unique()
    {
        // Create existing user
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_is_required()
    {
        $response = $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_password_confirmation_must_match()
    {
        $response = $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_password_follows_laravel_default_rules()
    {
        $response = $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '123', // Too short
                'password_confirmation' => '123',
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_fires_registered_event()
    {
        \Event::fake();

        $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        \Event::assertDispatched(\Illuminate\Auth\Events\Registered::class);
    }

    public function test_authenticated_users_cannot_access_registration()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/register');
        
        // Should redirect away from registration (guest middleware)
        $response->assertRedirect('/dashboard');
    }

    public function test_sanctum_integration_ready()
    {
        $this->withoutMiddleware([
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $user = User::where('email', 'test@example.com')->first();
        
        // Verify user can create tokens (Sanctum integration)
        $token = $user->createToken('test-token');
        $this->assertNotNull($token);
        $this->assertNotNull($token->plainTextToken);
    }
}
