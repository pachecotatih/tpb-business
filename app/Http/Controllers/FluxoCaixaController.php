<?php

namespace App\Http\Controllers;

use App\Models\FluxoCaixa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FluxoCaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $saldo = 0;
        $total_entradas = 0;
        $total_saidas = 0;
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Usuário não encontrado'], 404);
            }
            $forma_pagamento_filter = $request->query('forma_pagamento');
            $tipo_movimentacao_filter = $request->query('tipo_movimentacao');
            $data_registro_inicio = $request->query('data_registro_inicio') ?? Carbon::now()->subMonth()->format('Y-m-d H:i:s');
            $data_registro_fim = $request->query('data_registro_fim') ?? Carbon::now()->format('Y-m-d H:i:s');

            $fluxoCaixa = FluxoCaixa::query();

            if ($forma_pagamento_filter) {
                $fluxoCaixa->where('forma_pagamento', $forma_pagamento_filter);
            }

            if ($tipo_movimentacao_filter) {
                $fluxoCaixa->where('tipo_movimentacao', $tipo_movimentacao_filter);
            }

            if($data_registro_inicio && $data_registro_fim) {
                $fluxoCaixa->whereBetween('data_pagamento', [
                    Carbon::parse($data_registro_inicio)->startOfDay(),
                    Carbon::parse($data_registro_fim)->endOfDay(),
                ]);
            } else if ($data_registro_inicio) {
                $fluxoCaixa->where('data_pagamento', '>=', $data_registro_inicio);
            } else if ($data_registro_fim) {
                $fluxoCaixa->where('data_pagamento', '<=', $data_registro_fim);
            }

            $fluxoCaixa->where('user_id', $user->id);

            // Calcula os totais diretamente no banco de dados via SQL (extremamente rápido)
            $total_entradas = (clone $fluxoCaixa)->where('tipo_movimentacao', 'entrada')->sum('valor');
            $total_saidas = (clone $fluxoCaixa)->where('tipo_movimentacao', 'saida')->sum('valor');
            $saldo = $total_entradas - $total_saidas;

            $fluxoCaixaList = $fluxoCaixa->get()->map(function ($item) {
                if ($item->tipo_movimentacao === 'saida') {
                    $item->valor = $item->valor * (-1);
                }
                return $item;
            });

            return response()->json([
                'fluxo_caixa_list' => $fluxoCaixaList,
                'saldo' => $saldo,
                'total_entradas' => $total_entradas,
                'total_saidas' => $total_saidas * (-1)
            ], 200);


        } catch (\Throwable $th) {
            Log::error('FluxoCaixaController::index - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao buscar movimentações de caixa.'
            ], 500);
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
                'descricao' => 'required',
                'valor' => 'required|numeric',
                'tipo_movimentacao' => 'required|in:entrada,saida',
                'forma_pagamento' => 'required',
                'pago' => 'nullable|boolean',
                'data_vencimento' => [Rule::requiredIf(function () use ($request) {
                    return $request->tipo_movimentacao === 'saida';
                })],
                'data_pagamento' => [Rule::requiredIf(function () use ($request) {
                    return $request->tipo_movimentacao === 'entrada';
                })],
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }

            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Usuário não encontrado'], 404);
            }
            $fluxoCaixa = new FluxoCaixa();
            $fluxoCaixa->descricao = $request->descricao;
            $fluxoCaixa->valor = $request->valor;
            $fluxoCaixa->tipo_movimentacao = $request->tipo_movimentacao;
            $fluxoCaixa->forma_pagamento = $request->forma_pagamento;
            $fluxoCaixa->data_vencimento = $request->data_vencimento;
            $fluxoCaixa->data_pagamento = ($request->tipo_movimentacao == 'saida') ? Carbon::now() :$request->data_pagamento;
            $fluxoCaixa->pago = $request->pago ?? false;
            $fluxoCaixa->observacao = $request->observacao;
            $fluxoCaixa->user_id = $user->id;
            if(!empty($request->cliente_id)){
                $fluxoCaixa->cliente_id = $request->cliente_id;
            }
            if(!empty($request->agendamento_id)){
                $fluxoCaixa->agendamento_id = $request->agendamento_id;
            }
            $fluxoCaixa->save();

            return response()->json($fluxoCaixa);
        } catch (\Throwable $th) {
            Log::error('FluxoCaixaController::store - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao criar movimentação de caixa.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uid)
    {
        try {
            $fluxocaixa = FluxoCaixa::where('uid', $uid)->first();
            if(!$fluxocaixa) {
                return response()->json([
                    'message' => 'Movimentação de caixa nao encontrada.'
                ], 404);
            }
            return response()->json($fluxocaixa);
        } catch (\Throwable $th) {
            Log::error('FluxoCaixaController::show - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao buscar movimentação de caixa.'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
                'descricao' => 'required',
                'valor' => 'required|numeric',
                'tipo_movimentacao' => 'required|in:entrada,saida',
                'forma_pagamento' => 'required',
                'pago' => 'nullable|boolean',
                'data_vencimento' => [Rule::requiredIf(function () use ($request) {
                    return $request->tipo_movimentacao === 'saida';
                })],
                'data_pagamento' => [Rule::requiredIf(function () use ($request) {
                    return $request->tipo_movimentacao === 'entrada';
                })],
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }

            $fluxoCaixa = FluxoCaixa::where('uid', $uid)->first();
            $fluxoCaixa->descricao = $request->descricao;
            $fluxoCaixa->valor = $request->valor;
            $fluxoCaixa->tipo_movimentacao = $request->tipo_movimentacao;
            $fluxoCaixa->forma_pagamento = $request->forma_pagamento;
            $fluxoCaixa->data_vencimento = $request->data_vencimento;
            $fluxoCaixa->data_pagamento = $request->data_pagamento;
            if(isset($request->pago)){
                $fluxoCaixa->pago = $request->pago;
            }
            if(!empty($request->cliente_id)){
                $fluxoCaixa->cliente_id = $request->cliente_id;
            }
            if(!empty($request->agendamento_id)){
                $fluxoCaixa->agendamento_id = $request->agendamento_id;
            }
            $fluxoCaixa->observacao = $request->observacao;
            $fluxoCaixa->save();

            return response()->json($fluxoCaixa);
        } catch (\Throwable $th) {
            Log::error('FluxoCaixaController::update - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao atualizar movimentação de caixa.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $fluxoCaixa = FluxoCaixa::where('uid', $id)->first();
            $fluxoCaixa->delete();
            return response()->json([
                'message' => 'Movimentação de caixa deletada com sucesso.'
            ]);
        } catch (\Throwable $th) {
            Log::error('FluxoCaixaController::destroy - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao deletar movimentação de caixa.'
            ], 500);
        }
    }
}
