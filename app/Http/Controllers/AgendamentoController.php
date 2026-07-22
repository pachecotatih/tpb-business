<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\FluxoCaixa;
use App\Models\Servico;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AgendamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = User::where('uid', $request->header('user'))->select('id')->first();
            if(!$user) {
                return response()->json(['message' => 'Usuário não encontrado'], 404);
            }
            $agendamentos = Agendamento::with([
                'cliente:id,uid,nome',
                'servicos:uid,nome,valor_padrao,duracao_padrao'
            ])->where('user_id', $user->id)->get()->map(function ($agendamento) {
                foreach ($agendamento->servicos as $servico) {
                    $servico->duracao_padrao = $servico->pivot->duracao_servico;
                    $servico->valor_padrao = $servico->pivot->valor_servico;
                }
                return $agendamento;
            });

            return response()->json($agendamentos);
        } catch (\Throwable $th) {
            Log::error('AgendamentoController::index - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json(['message' => 'Erro ao buscar agendamentos: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'valor_total' => 'nullable|numeric',
                'data_inicio' => 'required',
                'data_fim' => 'required',
                'status' => 'nullable|in:agendado,concluido,cancelado',
                'cliente_id' => 'required|exists:clientes,id',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }

            $user = User::where('uid', $request->header('user'))->select('id')->first();
            if(!$user) {
                return response()->json(['message' => 'Usuário não encontrado'], 404);
            }

            $agendamento = new Agendamento();
            $agendamento->valor_total = $request->valor_total;
            $agendamento->data_inicio = $request->data_inicio;
            $agendamento->data_fim = $request->data_fim;
            $agendamento->status = $request->status ?? 'agendado';
            $agendamento->user_id = $user->id;
            $agendamento->cliente_id = $request->cliente_id;
            $agendamento->save();
            foreach ($request->servicos as $servico) {
                $servico_exists = null;

                if (!empty($servico['uid'])) {
                    $servico_exists = Servico::where('uid', $servico['uid'])->first();
                }
                if (!$servico_exists) {
                    $servico_exists = new Servico();
                    $servico_exists->nome = $servico['nome'];
                    $servico_exists->valor_padrao = $servico['valor_padrao'];
                    $servico_exists->duracao_padrao = $servico['duracao_padrao'];
                    $servico_exists->user_id = $user->id;
                    $servico_exists->save();
                }

                $agendamento->servicos()->syncWithoutDetaching([$servico_exists->id => [
                    'valor_servico' => $servico['valor_padrao'],
                    'duracao_servico' => $servico['duracao_padrao']
                ]]);
            }

            return response()->json($agendamento);
        } catch (\Throwable $th) {
            Log::error('AgendamentoController::store - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao criar agendamento.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uid)
    {
        try {
            $agendamento = Agendamento::with([
                'cliente:id,uid,nome,telefone,email',
                'servicos'
            ])->where('uid', $uid)->first();
            if (!$agendamento) {
                return response()->json([
                    'message' => 'Agendamento nao encontrado.'
                ], 404);
            }
            $clientes = Cliente::where('user_id', $agendamento->user_id)->get();
            $agendamento->clientes = $clientes;
            foreach ($agendamento->servicos as $servico) {
                $servico->duracao_padrao = $servico->pivot->duracao_servico;
                $servico->valor_padrao = $servico->pivot->valor_servico;
            }
            return response()->json($agendamento);
        } catch (\Throwable $th) {
            Log::error('AgendamentoController::show - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao buscar agendamento.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $uid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uid)
    {
        try {
            $validation = Validator::make($request->all(), [
                'valor_total' => 'nullable|numeric',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'status' => 'nullable|in:agendado,concluido,cancelado',
                'cliente_id' => 'required|exists:clientes,id',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }

            $agendamento = Agendamento::where('uid', $uid)->first();
            if (!$agendamento) {
                return response()->json([
                    'message' => 'Agendamento nao encontrado.'
                ], 404);
            }

            $agendamento->valor_total = $request->valor_total;
            $agendamento->data_inicio = $request->data_inicio;
            $agendamento->data_fim = $request->data_fim;
            $agendamento->status = $request->status ?? 'agendado';
            $agendamento->cliente_id = $request->cliente_id;
            $agendamento->save();
            $agendamento->servicos()->detach();
            foreach ($request->servicos as $servico) {
                $servico_exists = null;

                if (!empty($servico['uid'])) {
                    $servico_exists = Servico::where('uid', $servico['uid'])->first();
                }
                if (!$servico_exists) {
                    $servico_exists = new Servico();
                    $servico_exists->nome = $servico['nome'];
                    $servico_exists->valor_padrao = $servico['valor_padrao'];
                    $servico_exists->duracao_padrao = $servico['duracao_padrao'];
                    $servico_exists->user_id = $agendamento->user_id;
                    $servico_exists->save();
                }

                $agendamento->servicos()->syncWithoutDetaching([$servico_exists->id => [
                    'valor_servico' => $servico['valor_padrao'],
                    'duracao_servico' => $servico['duracao_padrao']
                ]]);
            }
            if($agendamento->status == 'concluido') {
                $cliente = Cliente::where('id', $agendamento->cliente_id)->first();
                $agendamento->data_fim = Carbon::now()->format('Y-m-d H:i:s');
                $agendamento->save();

                $agendamento->fluxoCaixa()->create([
                    'user_id' => $agendamento->user_id,
                    'descricao' => $cliente->nome,
                    'cliente_id' => $agendamento->cliente_id,
                     'tipo_movimentacao' => 'entrada',
                     'valor' => $agendamento->valor_total,
                     'data_pagamento' => $agendamento->data_fim,
                     'forma_pagamento' => $request->forma_pagamento ?? 'dinheiro',
                     'observacao' => ($agendamento->servicos->count() > 0)?'Serviços: ' . $agendamento->servicos->pluck('nome')->implode(', ').'.':'',
                ]);
            }
            return response()->json($agendamento);
        } catch (\Throwable $th) {
            Log::error('AgendamentoController::update - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao atualizar agendamento.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uid)
    {
        try {
            $agendamento = Agendamento::where('uid', $uid)->first();
            if (!$agendamento) {
                return response()->json([
                    'message' => 'Agendamento nao encontrado.'
                ], 404);
            }
            $agendamento->delete();
        } catch (\Throwable $th) {
            Log::error('AgendamentoController::destroy - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao deletar agendamento.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
