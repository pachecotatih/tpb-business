<?php

namespace Tests\Feature;

use App\Models\Servico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ServicoTest extends TestCase
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
    public function test_index_servicos_success(): void
    {
        Servico::factory()->count(5)->create(['user_id' => $this->user->id]);
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/servico');
        $response->assertStatus(200);

        $this->assertCount(5, $response->json());
    }

    public function test_index_servicos_user_not_found(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => '00000000-0000-0000-0000-000000000000'
        ])->getJson('/api/servico');
        $response->assertStatus(500);
    }

    public function test_show_servico_success(): void
    {
        $servico = Servico::factory()->create(['user_id' => $this->user->id]);
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/servico/' . $servico->uid);
        $response->assertStatus(200);
        $this->assertEquals($servico->nome, $response->json('nome'));
    }

    public function test_show_servico_not_found(): void
    {
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->getJson('/api/servico/00000000-0000-0000-0000-000000000000');
        $response->assertStatus(404);
    }

    function test_store_servico_success() : void {
        $servico = Servico::factory()->make();
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/servico', $servico->toArray());

        $response->assertStatus(200);

        $servico_bd = Servico::latest()->first();

        $this->assertEquals($servico->nome, $servico_bd->nome);
        $this->assertEquals($servico->valor_padrao, $servico_bd->valor_padrao);
        $this->assertEquals($servico->duracao_padrao, $servico_bd->duracao_padrao);
        $this->assertEquals($servico->ativo, $servico_bd->ativo);
    }

    function test_store_servico_failed_validation() : void {
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->postJson('/api/servico', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nome']);
    }

    function test_update_servico_success() {
        $servico = Servico::factory()->create(['user_id' => $this->user->id]);
        $servico->nome = 'Novo Nome';
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/servico/' . $servico->uid, $servico->toArray());
        $response->assertStatus(200);

        $servico_bd = Servico::where('uid', $servico->uid)->first();
        $this->assertEquals('Novo Nome', $servico_bd->nome);
    }

    function test_update_servico_not_found() {
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/servico/00000000-0000-0000-0000-000000000000', ['nome' => 'Novo Nome']);
        $response->assertStatus(404);
    }

    function test_update_servico_validation_failed() {
        $servico = Servico::factory()->create(['user_id' => $this->user->id]);
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->putJson('/api/servico/' . $servico->uid, []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nome']);
    }

    function test_destroy_servico_success() {
        $servico = Servico::factory()->create(['user_id' => $this->user->id]);
        $token = JWTAuth::fromUser($this->user);
        $response = $this->withHeaders([
            'Authorization'=> 'Bearer ' . $token,
            'user' => $this->user->uid
        ])->deleteJson('/api/servico/' . $servico->uid);
        $response->assertStatus(200);

        $servico_bd = Servico::where('uid', $servico->uid)->first();
        $this->assertNull($servico_bd);
    }

}
