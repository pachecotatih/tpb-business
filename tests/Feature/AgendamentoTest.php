<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AgendamentoTest extends TestCase
{
    use RefreshDatabase;
    private User $user;

    public function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * A basic feature test example.
     */
    public function test_index_agendamentos_success(): void
    {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $servico = Servico::factory()->create(['user_id' => $this->user->id]);
        $agendamentos = Agendamento::factory()->count(5)->create(['user_id' => $this->user->id, 'cliente_id' => $cliente->id]);
        $agendamentos->each(function ($agendamento) use ($servico) {
            $agendamento->servicos()->attach([
                $servico->id => [
                    'valor_servico' => $servico->valor_padrao,
                    'duracao_servico' => $servico->duracao_padrao
                ]
            ]);
        });
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/agendamento');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json());
        $this->assertArrayHasKey('servicos', $response->json()[0]);
        $this->assertArrayHasKey('cliente', $response->json()[0]);
    }

    public function test_store_agendamentos_success(): void {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $servicos = Servico::factory()->count(3)->make(['user_id' => $this->user->id]);
        $agendamento = Agendamento::factory()->make(['user_id' => $this->user->id, 'cliente_id' => $cliente->id]);
        $token = JWTAuth::fromUser($this->user);
        $agendamento->servicos = $servicos;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/agendamento', $agendamento->toArray());

        $response->assertStatus(200);

        $agendamento_bd = Agendamento::latest()->first();
        $servicos_agendamento = $agendamento_bd->servicos;
        $this->assertNotNull($agendamento_bd);
        $this->assertEquals($agendamento->nome, $agendamento_bd->nome);
        $this->assertEquals($agendamento->valor_total, $agendamento_bd->valor_total);
        $this->assertEquals($agendamento->data_inicio, $agendamento_bd->data_inicio);
        $this->assertEquals($agendamento->data_fim, $agendamento_bd->data_fim);
        $this->assertEquals($agendamento->status, $agendamento_bd->status);
        $this->assertEquals($agendamento->user_id, $agendamento_bd->user_id);
        $this->assertEquals($agendamento->cliente_id, $agendamento_bd->cliente_id);
        $this->assertArrayHasKey('nome', $servicos_agendamento[0]);
        $this->assertCount(3, $servicos_agendamento);
    }
    public function test_store_agendamentos_failed_validation(): void {
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/agendamento', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data_inicio', 'data_fim', 'cliente_id']);
    }

    public function test_store_agendamento_servicos_existentes() {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $servicos = Servico::factory()->count(3)->create(['user_id' => $this->user->id]);
        $agendamento = Agendamento::factory()->make(['user_id' => $this->user->id, 'cliente_id' => $cliente->id]);
        $token = JWTAuth::fromUser($this->user);
        $agendamento->servicos = $servicos;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/agendamento', $agendamento->toArray());

        $response->assertStatus(200);

        $agendamento_bd = Agendamento::latest()->first();
        $servicos_agendamento = $agendamento_bd->servicos;
        $this->assertNotNull($agendamento_bd);
        $this->assertEquals($agendamento->valor_total, $agendamento_bd->valor_total);
        $this->assertEquals($agendamento->data_inicio, $agendamento_bd->data_inicio);
        $this->assertEquals($agendamento->data_fim, $agendamento_bd->data_fim);
        $this->assertEquals($agendamento->status, $agendamento_bd->status);
        $this->assertEquals($agendamento->user_id, $agendamento_bd->user_id);
        $this->assertEquals($agendamento->cliente_id, $agendamento_bd->cliente_id);
        $this->assertArrayHasKey('nome', $servicos_agendamento[0]);
        $this->assertCount(3, $servicos_agendamento);
    }

    public function test_update_agendamento_success() {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $servicos = Servico::factory()->count(3)->create(['user_id' => $this->user->id]);
        $agendamento = Agendamento::factory()->create(['user_id' => $this->user->id, 'cliente_id' => $cliente->id, 'status'=>'agendado']);
        $token = JWTAuth::fromUser($this->user);
        $agendamento->status = 'concluido';
        $agendamento->servicos = $servicos;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/agendamento/' . $agendamento->uid, $agendamento->toArray());

        $response->assertStatus(200);

        $agendamento_bd = Agendamento::latest()->first();
        $servicos_agendamento = $agendamento_bd->servicos;

        $this->assertNotNull($agendamento_bd);
        $this->assertEquals($agendamento->valor_total, $agendamento_bd->valor_total);
        $this->assertEquals($agendamento->data_inicio, $agendamento_bd->data_inicio);
        $this->assertEquals($agendamento->data_fim, $agendamento_bd->data_fim);
        $this->assertEquals('concluido', $agendamento_bd->status);
        $this->assertEquals($agendamento->user_id, $agendamento_bd->user_id);
        $this->assertEquals($agendamento->cliente_id, $agendamento_bd->cliente_id);
        $this->assertArrayHasKey('nome', $servicos_agendamento[0]);
        $this->assertCount(3, $servicos_agendamento);
    }

    public function test_update_agendamento_failed_validation() : void {
        $agendamento = Agendamento::factory()->create(['user_id' => $this->user->id]);
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/agendamento/' . $agendamento->uid, []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['data_inicio', 'data_fim', 'cliente_id']);
    }

    public function test_update_agendamento_failed_validation_status() : void {
        $agendamento = Agendamento::factory()->create(['user_id' => $this->user->id]);
        $token = JWTAuth::fromUser($this->user);
        $agendamento->status = 'invalid';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/agendamento/' . $agendamento->uid, $agendamento->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_update_agendamento_servicos_inexistentes() {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $servicos = Servico::factory()->count(3)->make(['user_id' => $this->user->id]);
        $agendamento = Agendamento::factory()->create(['user_id' => $this->user->id, 'cliente_id' => $cliente->id, 'status'=>'agendado']);
        $token = JWTAuth::fromUser($this->user);
        $agendamento->status = 'concluido';
        $agendamento->servicos = $servicos;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/agendamento/' . $agendamento->uid, $agendamento->toArray());

        $response->assertStatus(200);

        $agendamento_bd = Agendamento::latest()->first();
        $servicos_agendamento = $agendamento_bd->servicos;

        $this->assertNotNull($agendamento_bd);
        $this->assertEquals($agendamento->valor_total, $agendamento_bd->valor_total);
        $this->assertEquals($agendamento->data_inicio, $agendamento_bd->data_inicio);
        $this->assertEquals($agendamento->data_fim, $agendamento_bd->data_fim);
        $this->assertEquals('concluido', $agendamento_bd->status);
        $this->assertEquals($agendamento->user_id, $agendamento_bd->user_id);
        $this->assertEquals($agendamento->cliente_id, $agendamento_bd->cliente_id);
        $this->assertArrayHasKey('nome', $servicos_agendamento[0]);
        $this->assertCount(3, $servicos_agendamento);
    }
    public function test_update_agendamento_servicos_inexistentes_existentes() {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $servicos_exists = Servico::factory()->count(3)->create(['user_id' => $this->user->id]);
        $servicos_inexists = Servico::factory()->count(3)->make(['user_id' => $this->user->id]);
        $servicos = collect($servicos_exists->all())->merge($servicos_inexists)->values();
        $agendamento = Agendamento::factory()->create(['user_id' => $this->user->id, 'cliente_id' => $cliente->id, 'status'=>'agendado']);
        $token = JWTAuth::fromUser($this->user);
        $agendamento->status = 'concluido';
        $agendamento->servicos = $servicos;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/agendamento/' . $agendamento->uid, $agendamento->toArray());

        $response->assertStatus(200);

        $agendamento_bd = Agendamento::latest()->first();
        $servicos_agendamento = $agendamento_bd->servicos;

        $this->assertNotNull($agendamento_bd);
        $this->assertEquals($agendamento->valor_total, $agendamento_bd->valor_total);
        $this->assertEquals($agendamento->data_inicio, $agendamento_bd->data_inicio);
        $this->assertEquals($agendamento->data_fim, $agendamento_bd->data_fim);
        $this->assertEquals('concluido', $agendamento_bd->status);
        $this->assertEquals($agendamento->user_id, $agendamento_bd->user_id);
        $this->assertEquals($agendamento->cliente_id, $agendamento_bd->cliente_id);
        $this->assertArrayHasKey('nome', $servicos_agendamento[0]);
        $this->assertCount(6, $servicos_agendamento);
    }
    public function test_update_agendamento_servicos_inexistentes_existentes_no_registro() {
        $cliente = Cliente::factory()->create(['user_id' => $this->user->id]);
        $servicos_exists = Servico::factory()->count(3)->create(['user_id' => $this->user->id]);
        $servicos_inexists = Servico::factory()->count(3)->make(['user_id' => $this->user->id]);

        $agendamento = Agendamento::factory()->create(['user_id' => $this->user->id, 'cliente_id' => $cliente->id, 'status'=>'agendado']);
        foreach ($servicos_exists as $servico) {
            $agendamento->servicos()->attach([$servico->id=>['valor_servico' => $servico->valor_padrao, 'duracao_servico' => $servico->duracao_padrao]]);
        }
        $servicos = collect($servicos_exists->all())->merge($servicos_inexists)->values();
        $token = JWTAuth::fromUser($this->user);
        $agendamento->status = 'concluido';
        $agendamento->servicos = $servicos;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/agendamento/' . $agendamento->uid, $agendamento->toArray());

        $response->assertStatus(200);

        $agendamento_bd = Agendamento::latest()->first();
        $servicos_agendamento = $agendamento_bd->servicos;

        $this->assertNotNull($agendamento_bd);
        $this->assertEquals($agendamento->valor_total, $agendamento_bd->valor_total);
        $this->assertEquals($agendamento->data_inicio, $agendamento_bd->data_inicio);
        $this->assertEquals($agendamento->data_fim, $agendamento_bd->data_fim);
        $this->assertEquals('concluido', $agendamento_bd->status);
        $this->assertEquals($agendamento->user_id, $agendamento_bd->user_id);
        $this->assertEquals($agendamento->cliente_id, $agendamento_bd->cliente_id);
        $this->assertArrayHasKey('nome', $servicos_agendamento[0]);
        $this->assertCount(6, $servicos_agendamento);
    }

    public function test_destroy_agendamento_success() : void {
        $agendamento = Agendamento::factory()->create(['user_id' => $this->user->id]);
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->delete('/api/agendamento/' . $agendamento->uid);
        $response->assertStatus(200);
        $agendamento_bd = Agendamento::where('uid', $agendamento->uid)->first();
        $this->assertNull($agendamento_bd);
    }
}
