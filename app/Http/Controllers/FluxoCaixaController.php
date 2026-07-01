<?php

namespace App\Http\Controllers;

use App\Models\FluxoCaixa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class FluxoCaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }

            $user = User::where('uid', $request->header('user'))->first();
            if (!$user) {
                throw new \Exception('Usuário não encontrado.', 404);
            }
            $fluxoCaixa = new FluxoCaixa();
            $fluxoCaixa->descricao = $request->descricao;
            $fluxoCaixa->valor = $request->valor;
            $fluxoCaixa->tipo_movimentacao = $request->tipo_movimentacao;
            $fluxoCaixa->forma_pagamento = $request->forma_pagamento;
            $fluxoCaixa->data_vencimento = $request->data_vencimento;
            $fluxoCaixa->data_pagamento = $request->data_pagamento;
            $fluxoCaixa->pago = $request->pago ?? false;
            $fluxoCaixa->observacao = $request->observacao;
            $fluxoCaixa->user_id = $user->id;
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
    public function show(string $id)
    {
        //
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
        //
    }
}
