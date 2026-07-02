<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_index_cliente_success(): void
    {
        Cliente::factory()->count(3)->create(['user_id' => $this->user->id]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $this->token,
            'user' => $this->user->uid
        ])->getJson('/api/cliente');
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    public function test_show_cliente_success(): void
    {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $this->token,
            'user' => $this->user->uid
        ])->getJson('/api/cliente/' . $cliente->uid);
        $response->assertStatus(200);
        $this->assertEquals($cliente->nome, $response->json('nome'));
    }

    public function test_show_cliente_not_found(): void
    {
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $this->token,
            'user' => $this->user->uid
        ])->getJson('/api/cliente/00000000-0000-0000-0000-000000000000');
        $response->assertStatus(404);
    }

    public function test_store_cliente_success(): void {
        $cliente = Cliente::factory()->make(['user_id' => $this->user->id]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $this->token,
            'user' => $this->user->uid
        ])->postJson('/api/cliente', $cliente->toArray());
        $response->assertStatus(200);

        $cliente_bd = Cliente::where('uid', $response->json('uid'))->first();
        $this->assertEquals($cliente->nome, $cliente_bd->nome);
        $this->assertEquals($cliente->telefone, $cliente_bd->telefone);
        $this->assertEquals($cliente->email, $cliente_bd->email);
        $this->assertEquals($cliente->tipo, $cliente_bd->tipo);
        $this->assertEquals($cliente->endereco, $cliente_bd->endereco);
        $this->assertEquals($cliente->data_nascimento, $cliente_bd->data_nascimento);
        $this->assertEquals($cliente->documento, $cliente_bd->documento);
        $this->assertEquals($cliente->observacao, $cliente_bd->observacao);
    }

    public function test_store_cliente_validation_error(): void {
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer '. $this->token,
            'user' => $this->user->uid
        ])->postJson('/api/cliente', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nome');
    }

    public function test_update_cliente_success(): void {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $cliente->nome = 'Novo Nome';
        $cliente->tipo = 'PF';
        $cliente->documento = '123.456.789-00';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer '. $this->token,
            'user' => $this->user->uid
        ])->putJson('/api/cliente/' . $cliente->uid, $cliente->toArray());
        $response->assertStatus(200);

        $cliente_bd = Cliente::find($cliente->id);
        $this->assertEquals('Novo Nome', $cliente_bd->nome);
        $this->assertEquals('PF', $cliente_bd->tipo);
        $this->assertEquals('123.456.789-00', $cliente_bd->documento);
    }

    public function test_update_cliente_not_found(): void {
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer '. $this->token,
            'user' => $this->user->uid
        ])->putJson('/api/cliente/00000000-0000-0000-0000-000000000000', ['nome' => 'Novo Nome']);
        $response->assertStatus(404);
    }

    public function test_update_cliente_validation_error(): void {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer '. $this->token,
            'user' => $this->user->uid
        ])->putJson('/api/cliente/' . $cliente->uid, ['nome' => '']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nome');
    }

    public function test_destroy_cliente_success(): void {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer '. $this->token,
            'user' => $this->user->uid
        ])->deleteJson('/api/cliente/' . $cliente->uid);
        $response->assertStatus(200);

        $cliente_bd = Cliente::where('uid', $cliente->uid)->first();
        $this->assertNull($cliente_bd);
    }
}
