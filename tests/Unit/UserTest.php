<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user);
    }

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $user = new User();
        $expected = ['name', 'email', 'password'];
        $this->assertEquals($expected, $user->getFillable());
    }

    #[Test]
    public function it_has_hidden_attributes(): void
    {
        $user = new User();
        $expected = ['password', 'remember_token'];
        $this->assertEquals($expected, $user->getHidden());
    }

    #[Test]
    public function it_casts_attributes_correctly(): void
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    #[Test]
    public function it_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    #[Test]
    public function it_can_be_created_with_specific_attributes(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $user = User::factory()->create($userData);
        
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    #[Test]
    public function it_hashes_password_automatically(): void
    {
        $user = User::factory()->create(['password' => 'plaintext']);
        
        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(password_verify('plaintext', $user->password));
    }

    #[Test]
    public function it_hides_sensitive_attributes_in_array(): void
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();
        
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
    }

    #[Test]
    public function it_has_sessions_relationship_method(): void
    {
        $user = new User();
        
        $this->assertTrue(method_exists($user, 'sessions'));
        // Note: Session model relationship will be tested when Session model is implemented in future story
    }

    #[Test]
    public function it_has_sanctum_api_tokens_trait(): void
    {
        $user = User::factory()->create();
        
        $this->assertTrue(method_exists($user, 'createToken'));
        $this->assertTrue(method_exists($user, 'tokens'));
    }

    #[Test]
    public function it_can_create_api_tokens(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        $this->assertNotNull($token);
        $this->assertEquals('test-token', $token->accessToken->name);
    }

    #[Test]
    public function it_enforces_unique_email_constraint(): void
    {
        $email = 'unique@example.com';
        User::factory()->create(['email' => $email]);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => $email]);
    }

    #[Test]
    public function factory_creates_unverified_users(): void
    {
        $user = User::factory()->unverified()->create();
        
        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function factory_creates_verified_users_by_default(): void
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->email_verified_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }
} 