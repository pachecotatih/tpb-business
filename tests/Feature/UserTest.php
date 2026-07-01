<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
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

    public function test_user_update_success() : void {

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $user->email = 'novoemail@example.com';
        $user->name = 'Novo Nome';
        $user->moeda = "$";

        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $user->uid
        ])->putJson('/api/user', $user->toArray());
        $response->assertStatus(200);

        $user_bd = User::where('uid', $user->uid)->first();
        $this->assertEquals($user->email, $user_bd->email);
        $this->assertEquals($user->name, $user_bd->name);
        $this->assertEquals($user->moeda, $user_bd->moeda);

    }

    public function test_user_update_validation_failed() : void {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $user->email = 'emailinvalido';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $user->uid
        ])->putJson('/api/user', $user->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }
    public function test_user_update_validation_failed_empty() : void {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $user->uid
        ])->putJson('/api/user', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'documento', 'telefone', 'moeda']);
    }

    public function test_user_update_validation_documento_unique_failed() : void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $token = JWTAuth::fromUser($user1);
        $user1->documento = $user2->documento;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $user1->uid
        ])->putJson('/api/user', $user1->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('documento');
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
            'user' => $user->uid
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
            'user' => 'abc123'
        ])->getJson('/api/user');

        $response->assertStatus(500);
    }

    public function test_changePasswordLogged_user_success() : void {

        $user = User::factory()->create([
            'password' => Hash::make('123456')
        ]);
        $token = JWTAuth::fromUser($user);
        $data = [
            'password' => '654321'
        ];
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $user->uid
        ])->postJson('/api/user/change-password', $data);
        $response->assertStatus(200);

        $user_bd = User::where('uid', $user->uid)->first();
        $this->assertTrue(Hash::check('654321', $user_bd->password));
    }

    public function test_changePasswordLogged_user_failed_validation_required() : void {

        $user = User::factory()->create([
            'password' => Hash::make('123456')
        ]);
        $token = JWTAuth::fromUser($user);
        $data = [];
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $user->uid
        ])->postJson('/api/user/change-password', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_changePasswordLogged_user_failed_min_validation() : void {

        $user = User::factory()->create([
            'password' => Hash::make('123456')
        ]);
        $token = JWTAuth::fromUser($user);
        $data = [
            'password' => '123'
        ];
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $user->uid
        ])->postJson('/api/user/change-password', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_changePasswordLogged_user_failed_user_invalid() : void {
        $user = User::factory()->create([
            'password' => Hash::make('123456')
        ]);
        $token = JWTAuth::fromUser($user);
        $data = [
            'password' => '654321'
        ];
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => 'abc123'
        ])->postJson('/api/user/change-password', $data);
        $response->assertStatus(500);
    }

    public function test_changePasswordLogged_user_failed_user_unauthorized() : void {
        $user1 = User::factory()->create([
            'password' => Hash::make('123456')
        ]);
        $user2 = User::factory()->create();
        $token = JWTAuth::fromUser($user1);
        $data = [
            'password' => '654321'
        ];
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $user2->uid
        ])->postJson('/api/user/change-password', $data);
        $response->assertStatus(500);
    }

    public function test_forgotPassword_user_success() : void {
        $user = User::factory()->create();
        $data = [
            'email' => $user->email
        ];
        Notification::fake();
        $response = $this->postJson('/api/forgot-password', $data);
        $response->assertStatus(200);
    }

    public function test_forgotPassword_user_failed_validation_required() : void {
        $data = [];
        $response = $this->postJson('/api/forgot-password', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_forgotPassword_user_failed_validation_email() : void {
        $data = [
            'email' => 'invalid'
        ];
        $response = $this->postJson('/api/forgot-password', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_resetPassword_user_success() : void {
        Notification::fake();
        $user = User::factory()->create();
         Password::sendResetLink([
            'email' => $user->email,
        ]);
         Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function ($notification) use ($user) {
                $response = $this->postJson('/api/reset-password', [
                    'email' => $user->email,
                    'token' => $notification->getToken(),
                    'password' => 'NovaSenha123',
                    'password_confirmation' => 'NovaSenha123',
                ]);

                $response
                    ->assertStatus(200)
                    ->assertJson([
                        'message' => 'Senha alterada com sucesso.',
                    ]);

                $this->assertTrue(
                    Hash::check(
                        'NovaSenha123',
                        $user->fresh()->password
                    )
                );

                return true;
            }
        );
    }

    public function test_resetPassword_user_failed_validation_required() : void {
        $data = [];
        $response = $this->postJson('/api/reset-password', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['token', 'password']);
    }

    public function test_resetPassword_user_failed_validation_password() : void {
        $token = '123456789';
        $user = User::factory()->create();
        $data = [
            'token' => $token,
            'password' => '123'
        ];
        $response = $this->postJson('/api/reset-password', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_resetPassword_user_failed_token_invalid() : void {
        $token = '123456789';
        $user = User::factory()->create();
        $data = [
            'token' => 'invalid',
            'password' => '654321'
        ];
        $response = $this->postJson('/api/reset-password', $data);
        $response->assertStatus(422);
    }
}
