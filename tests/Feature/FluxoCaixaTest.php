<?php

namespace Tests\Feature;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\FluxoCaixa;
use App\Models\Servico;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FluxoCaixaTest extends TestCase
{

    use RefreshDatabase;

    private User $user;
    public function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_fluxocaixa_index_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        FluxoCaixa::factory()->count(5)->create(['user_id' => $this->user->id, 'valor' => 10, 'tipo_movimentacao' => 'entrada', 'forma_pagamento' => 'dinheiro', 'pago' => true, 'data_pagamento' => Carbon::now()->format('Y-m-d H:i:s')]);
        FluxoCaixa::factory()->count(3)->create(['user_id' => $this->user->id, 'valor' => 5, 'tipo_movimentacao' => 'saida', 'forma_pagamento' => 'cartao', 'pago' => false, 'data_vencimento' => Carbon::now()->format('Y-m-d H:i:s')]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/fluxocaixa');
        $response->assertStatus(200);
        $this->assertCount(8, $response->json('fluxo_caixa_list'));
        $this->assertEquals($response->json('saldo'), 35);
        $this->assertEquals($response->json('total_entradas'), 50);
        $this->assertEquals($response->json('total_saidas'), -15);
    }

    public function test_fluxocaixa_index_filter_forma_pagamento_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        FluxoCaixa::factory()->count(5)->create(['user_id' => $this->user->id, 'valor' => 10, 'tipo_movimentacao' => 'entrada', 'forma_pagamento' => 'dinheiro', 'pago' => true, 'data_pagamento' => Carbon::now()->format('Y-m-d H:i:s')]);
        FluxoCaixa::factory()->count(3)->create(['user_id' => $this->user->id, 'valor' => 5, 'tipo_movimentacao' => 'saida', 'forma_pagamento' => 'cartao', 'pago' => false, 'data_vencimento' => Carbon::now()->format('Y-m-d H:i:s')]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/fluxocaixa?forma_pagamento=dinheiro');
        $response->assertStatus(200);
        $this->assertCount(5, $response->json('fluxo_caixa_list'));
        $this->assertEquals($response->json('saldo'), 50);
        $this->assertEquals($response->json('total_entradas'), 50);
        $this->assertEquals($response->json('total_saidas'), 0);
    }

    public function test_fluxocaixa_index_filter_tipo_movimentacao_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        FluxoCaixa::factory()->count(5)->create(['user_id' => $this->user->id, 'valor' => 10, 'tipo_movimentacao' => 'entrada', 'forma_pagamento' => 'dinheiro', 'pago' => true, 'data_pagamento' => Carbon::now()->format('Y-m-d H:i:s')]);
        FluxoCaixa::factory()->count(3)->create(['user_id' => $this->user->id, 'valor' => 5, 'tipo_movimentacao' => 'saida', 'forma_pagamento' => 'cartao', 'pago' => false, 'data_vencimento' => Carbon::now()->format('Y-m-d H:i:s')]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/fluxocaixa?tipo_movimentacao=saida');
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('fluxo_caixa_list'));
        $this->assertEquals($response->json('saldo'), -15);
        $this->assertEquals($response->json('total_entradas'), 0);
        $this->assertEquals($response->json('total_saidas'), -15);
    }

    public function test_fluxocaixa_index_filter_data_registro_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        FluxoCaixa::factory()->count(5)->create(['user_id' => $this->user->id, 'valor' => 10, 'tipo_movimentacao' => 'entrada', 'forma_pagamento' => 'dinheiro', 'pago' => true, 'data_pagamento' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s')]);
        FluxoCaixa::factory()->count(5)->create(['user_id' => $this->user->id, 'valor' => 10, 'tipo_movimentacao' => 'entrada', 'forma_pagamento' => 'dinheiro', 'pago' => true, 'data_pagamento' => Carbon::now()->format('Y-m-d H:i:s')]);
        FluxoCaixa::factory()->count(3)->create(['user_id' => $this->user->id, 'valor' => 5, 'tipo_movimentacao' => 'saida', 'forma_pagamento' => 'cartao', 'pago' => false, 'data_vencimento' => Carbon::now()->format('Y-m-d H:i:s')]);
        $data_inicio = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');
        $data_fim = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/fluxocaixa?data_registro_inicio=' . $data_inicio . '&data_registro_fim=' . $data_fim);
        $response->assertStatus(200);
        $this->assertCount(8, $response->json('fluxo_caixa_list'));
        $this->assertEquals($response->json('saldo'), 35);
        $this->assertEquals($response->json('total_entradas'), 50);
        $this->assertEquals($response->json('total_saidas'), -15);
    }

    public function test_fluxocaixa_index_filter_data_registro_empty_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        FluxoCaixa::factory()->count(5)->create(['user_id' => $this->user->id, 'valor' => 10, 'tipo_movimentacao' => 'entrada', 'forma_pagamento' => 'dinheiro', 'pago' => true, 'data_pagamento' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s')]);
        FluxoCaixa::factory()->count(5)->create(['user_id' => $this->user->id, 'valor' => 10, 'tipo_movimentacao' => 'entrada', 'forma_pagamento' => 'dinheiro', 'pago' => true, 'data_pagamento' => Carbon::now()->subDays(4)->format('Y-m-d H:i:s')]);
        FluxoCaixa::factory()->count(3)->create(['user_id' => $this->user->id, 'valor' => 5, 'tipo_movimentacao' => 'saida', 'forma_pagamento' => 'cartao', 'pago' => false, 'data_vencimento' => Carbon::now()->format('Y-m-d H:i:s'),'data_pagamento' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s')]);
        $data_inicio = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');
        $data_fim = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/fluxocaixa?data_registro_inicio=' . $data_inicio . '&data_registro_fim=' . $data_fim);
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('fluxo_caixa_list'));
        $this->assertEquals($response->json('saldo'), 0);
        $this->assertEquals($response->json('total_entradas'), 0);
        $this->assertEquals($response->json('total_saidas'), 0);
    }

    public function test_fluxocaixa_show_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/fluxocaixa/' . $fluxoCaixa->uid);
        $response->assertStatus(200);
        $response->assertJson([
            'uid' => $fluxoCaixa->uid,
            'descricao' => $fluxoCaixa->descricao,
            'valor' => $fluxoCaixa->valor,
            'tipo_movimentacao' => $fluxoCaixa->tipo_movimentacao,
            'forma_pagamento' => $fluxoCaixa->forma_pagamento,
            'data_vencimento' => $fluxoCaixa->data_vencimento,
            'data_pagamento' => $fluxoCaixa->data_pagamento,
            'pago' => $fluxoCaixa->pago,
            'observacao' => $fluxoCaixa->observacao,
        ]);
    }
    /**
     * A basic feature test example.
     */
    public function test_fluxocaixa_store_saida_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->tipo_movimentacao = 'saida';
        $fluxoCaixa->data_vencimento = Carbon::now()->format('Y-m-d H:i:s');
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(200);

        $fluxoCaixa_bd = FluxoCaixa::latest()->first();
        $this->assertEquals($fluxoCaixa->descricao, $fluxoCaixa_bd->descricao);
        $this->assertEquals($fluxoCaixa->valor, $fluxoCaixa_bd->valor);
        $this->assertEquals($fluxoCaixa->tipo_movimentacao, $fluxoCaixa_bd->tipo_movimentacao);
        $this->assertEquals($fluxoCaixa->forma_pagamento, $fluxoCaixa_bd->forma_pagamento);
        $this->assertEquals($fluxoCaixa->pago, $fluxoCaixa_bd->pago);
        $this->assertEquals($fluxoCaixa->data_vencimento, $fluxoCaixa_bd->data_vencimento);

    }
    public function test_fluxocaixa_store_entrada_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->tipo_movimentacao = 'entrada';
        $fluxoCaixa->pago = true;
        $fluxoCaixa->data_pagamento = Carbon::now()->format('Y-m-d H:i:s');
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(200);

        $fluxoCaixa_bd = FluxoCaixa::latest()->first();
        $this->assertEquals($fluxoCaixa->descricao, $fluxoCaixa_bd->descricao);
        $this->assertEquals($fluxoCaixa->valor, $fluxoCaixa_bd->valor);
        $this->assertEquals($fluxoCaixa->tipo_movimentacao, $fluxoCaixa_bd->tipo_movimentacao);
        $this->assertEquals($fluxoCaixa->forma_pagamento, $fluxoCaixa_bd->forma_pagamento);
        $this->assertEquals($fluxoCaixa->pago, $fluxoCaixa_bd->pago);
        $this->assertEquals($fluxoCaixa->data_pagamento, $fluxoCaixa_bd->data_pagamento);

    }
    public function test_fluxocaixa_store_entrada_agendamento_pago_success(): void
    {
        $cliente = Cliente::factory()->create();
        $servico = Servico::factory()->create(
            [
                'user_id' => $this->user->id
            ]
        );
        $agendamento = Agendamento::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'agendado',
            'cliente_id' => $cliente->id
        ]);
        $agendamento->servicos()->attach([
            $servico->id => [
                'valor_servico' => $servico->valor_padrao,
                'duracao_servico' => $servico->duracao_padrao
            ]
        ]);
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->tipo_movimentacao = 'entrada';
        $fluxoCaixa->pago = true;
        $fluxoCaixa->data_pagamento = Carbon::now()->format('Y-m-d H:i:s');
        $fluxoCaixa->agendamento_id = $agendamento->id;
        $fluxoCaixa->cliente_id = $cliente->id;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(200);

        $fluxoCaixa_bd = FluxoCaixa::latest()->first();
        $this->assertEquals($fluxoCaixa->descricao, $fluxoCaixa_bd->descricao);
        $this->assertEquals($fluxoCaixa->valor, $fluxoCaixa_bd->valor);
        $this->assertEquals($fluxoCaixa->tipo_movimentacao, $fluxoCaixa_bd->tipo_movimentacao);
        $this->assertEquals($fluxoCaixa->forma_pagamento, $fluxoCaixa_bd->forma_pagamento);
        $this->assertEquals($fluxoCaixa->pago, $fluxoCaixa_bd->pago);
        $this->assertEquals($fluxoCaixa->data_pagamento, $fluxoCaixa_bd->data_pagamento);
        $this->assertEquals($fluxoCaixa->agendamento_id, $fluxoCaixa_bd->agendamento_id);
        $this->assertEquals($fluxoCaixa->cliente_id, $fluxoCaixa_bd->cliente_id);

    }
    public function test_fluxocaixa_store_validation_data_vencimento_required_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->tipo_movimentacao = 'saida';
        $fluxoCaixa->data_vencimento = null;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('data_vencimento');
    }

    public function test_fluxocaixa_store_validation_data_pagamento_required_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->tipo_movimentacao = 'entrada';
        $fluxoCaixa->data_pagamento = null;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('data_pagamento');
    }

    public function test_fluxocaixa_store_validation_required_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['descricao', 'valor', 'tipo_movimentacao', 'forma_pagamento']);
    }

    public function test_fluxocaixa_store_validation_tipo_movimentacao_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->tipo_movimentacao = 'invalid';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tipo_movimentacao');
    }

    public function test_fluxocaixa_store_validation_forma_pagamento_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->forma_pagamento = null;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('forma_pagamento');
    }

    public function test_fluxocaixa_store_validation_valor_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->valor = 'invalid';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('valor');
    }

    public function test_fluxocaixa_store_validation_pago_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->make();
        $fluxoCaixa->pago = 'invalid';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/fluxocaixa', $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('pago');
    }

    public function test_fluxocaixa_update_saida_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id,'valor'=>10.00, 'forma_pagamento' => 'dinheiro', 'tipo_movimentacao' => 'entrada', 'pago' => false]);
        $fluxoCaixa->descricao = 'Updated Description';
        $fluxoCaixa->valor = 20.00;
        $fluxoCaixa->tipo_movimentacao = 'saida';
        $fluxoCaixa->forma_pagamento = 'cartao';
        $fluxoCaixa->pago = true;
        $fluxoCaixa->data_vencimento = Carbon::now()->format('Y-m-d H:i:s');

        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/fluxocaixa/' . $fluxoCaixa->uid, $fluxoCaixa->toArray());
        $response->assertStatus(200);

        $fluxoCaixa_bd = FluxoCaixa::where('uid', $fluxoCaixa->uid)->first();
        $this->assertEquals('Updated Description', $fluxoCaixa_bd->descricao);
        $this->assertEquals(20.00, $fluxoCaixa_bd->valor);
        $this->assertEquals('saida', $fluxoCaixa_bd->tipo_movimentacao);
        $this->assertEquals('cartao', $fluxoCaixa_bd->forma_pagamento);
        $this->assertEquals(1, $fluxoCaixa_bd->pago);
        $this->assertEquals($fluxoCaixa->data_vencimento, $fluxoCaixa_bd->data_vencimento);
    }

    public function test_fluxocaixa_update_entrada_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id,'valor'=>10.00, 'forma_pagamento' => 'dinheiro', 'tipo_movimentacao' => 'saida', 'pago' => false]);
        $fluxoCaixa->descricao = 'Updated Description';
        $fluxoCaixa->valor = 20.00;
        $fluxoCaixa->tipo_movimentacao = 'entrada';
        $fluxoCaixa->forma_pagamento = 'cartao';
        $fluxoCaixa->pago = true;
        $fluxoCaixa->data_pagamento = Carbon::now()->format('Y-m-d H:i:s');

        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/fluxocaixa/' . $fluxoCaixa->uid, $fluxoCaixa->toArray());
        $response->assertStatus(200);

        $fluxoCaixa_bd = FluxoCaixa::where('uid', $fluxoCaixa->uid)->first();
        $this->assertEquals('Updated Description', $fluxoCaixa_bd->descricao);
        $this->assertEquals(20.00, $fluxoCaixa_bd->valor);
        $this->assertEquals('entrada', $fluxoCaixa_bd->tipo_movimentacao);
        $this->assertEquals('cartao', $fluxoCaixa_bd->forma_pagamento);
        $this->assertEquals(1, $fluxoCaixa_bd->pago);
        $this->assertEquals($fluxoCaixa->data_pagamento, $fluxoCaixa_bd->data_pagamento);
    }

    public function test_fluxocaixa_update_validation_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/fluxocaixa/' . $fluxoCaixa->uid, []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['descricao', 'valor', 'tipo_movimentacao', 'forma_pagamento']);
    }

    public function test_fluxocaixa_update_validation_tipo_movimentacao_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id]);
        $fluxoCaixa->tipo_movimentacao = 'invalid';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/fluxocaixa/' . $fluxoCaixa->uid, $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tipo_movimentacao');
    }

    public function test_fluxocaixa_update_validation_forma_pagamento_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id]);
        $fluxoCaixa->forma_pagamento = null;
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/fluxocaixa/' . $fluxoCaixa->uid, $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('forma_pagamento');
    }

    public function test_fluxocaixa_update_validation_valor_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id]);
        $fluxoCaixa->valor = 'invalid';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/fluxocaixa/' . $fluxoCaixa->uid, $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('valor');
    }

    public function test_fluxocaixa_update_validation_pago_failed(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id]);
        $fluxoCaixa->pago = 'invalid';
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/fluxocaixa/' . $fluxoCaixa->uid, $fluxoCaixa->toArray());
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('pago');
    }

    public function test_fluxocaixa_destroy_success(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $fluxoCaixa = FluxoCaixa::factory()->create(['user_id' => $this->user->id]);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->deleteJson('/api/fluxocaixa/' . $fluxoCaixa->uid);
        $response->assertStatus(200);

        $fluxoCaixa_bd = FluxoCaixa::where('uid', $fluxoCaixa->uid)->first();
        $this->assertNull($fluxoCaixa_bd);
    }
}
