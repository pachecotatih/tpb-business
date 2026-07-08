<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Servico;
use App\Models\User;
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
            $user = User::where('uid', $request->header('user'))->first();
            if(!$user) {
                return response()->json(['message' => 'Usuário não encontrado'], 404);
            }
            $agendamentos = Agendamento::with('servicos', 'cliente')->where('user_id', $user->id)->get();
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
                    'errors' => $validation->errors()->first()
                ], 422);
            }

            $user = User::where('uid', $request->header('user'))->first();
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

                if (!empty($servico->id)) {
                    $servico_exists = Servico::find($servico->id);
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
        //
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
                    'errors' => $validation->errors()->first()
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

                if (!empty($servico->id)) {
                    $servico_exists = Servico::find($servico->id);
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
