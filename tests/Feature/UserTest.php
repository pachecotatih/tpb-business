<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    use RefreshDatabase;
    private User $user;
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }


    /**
     * A basic feature test example.
     */
    public function test_user_store_success(): void
    {
        $user = User::factory()->make();

        $userInsert = array_merge($user->toArray(), ['password' => '123456']);
        $response = $this->postJson('/api/register', $userInsert);

        $response->assertStatus(201);
        $user_bd = User::where('email', $user->email)->first();
        $this->assertEquals($user->email, $user_bd->email);
    }

    public function test_user_store_validation_required_failed(): void
    {
        $user = User::factory()->make();
        $user->email = null;
        $userInsert = array_merge($user->toArray(), ['password' => '123456']);
        $response = $this->postJson('/api/register', $userInsert);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_user_store_validation_email_failed(): void
    {
        $user = User::factory()->make();
        $user->email = 'email';
        $userInsert = array_merge($user->toArray(), ['password' => '123456']);
        $response = $this->postJson('/api/register', $userInsert);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_user_store_validation_password_failed(): void
    {
        $user = User::factory()->make();
        $user->password = '123';
        $response = $this->postJson('/api/register', $user->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_user_login_success() : void {
        $user = User::factory()->create([
            'password' => Hash::make('123456')
        ]);
        $credentials = [
            'email' => $user->email,
            'password' => '123456',
            'device_id' =>  '123456789',
            'device_name' => 'celular_android'
        ];
        $response = $this->postJson('/api/login', $credentials);
        $response->assertStatus(200);
    }

    public function test_user_login_failed() : void {
        $user = User::factory()->create([
            'password' => Hash::make('123456')
        ]);
        $credentials = [
            'email' => $user->email,
            'password' => '1234567'
        ];
        $response = $this->postJson('/api/login', $credentials);
        $response->assertStatus(401);
    }

    public function test_index_user_success() : void {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user_uid' => $user->uid
        ])->getJson('/api/user');
        $response->assertStatus(200);

        $this->assertEquals($user->email, $response->json('email'));
    }

    public function test_index_user_failed() : void {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    public function test_index_user_uid_invalid(): void {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user_uid' => 'abc123'
        ])->getJson('/api/user');

        $response->assertStatus(500);
    }
}
