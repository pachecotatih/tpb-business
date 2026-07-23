<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServicoController extends Controller
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
            $servicos = Servico::where('user_id', $user->id)->orderByDesc('created_at')->get();
            return response()->json($servicos);
        } catch (\Throwable $th) {
            Log::error('ServicoController::index - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json(['message' => 'Erro ao buscar serviços: ' . $th->getMessage()], 500);
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
                'nome' => 'required',
                'valor_padrao' => 'nullable|numeric',
                'duracao_padrao' => 'nullable|string',
                'ativo' => 'nullable|boolean',
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

            $servico = new Servico();
            $servico->nome = $request->nome;
            $servico->valor_padrao = $request->valor_padrao;
            $servico->duracao_padrao = $request->duracao_padrao;
            $servico->ativo = $request->ativo;
            $servico->user_id = $user->id;
            $servico->save();

            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }

            return response()->json($servico);
        } catch (\Throwable $th) {
            Log::error('ServicoController::store - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao criar serviço.',
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
            $servico = Servico::where('uid', $uid)->first();
            if(!$servico) {
                return response()->json([
                    'message' => 'Serviço nao encontrado.'
                ], 404);
            }
            return response()->json($servico);
        } catch (\Throwable $th) {
            Log::error('ServicoController::show - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao buscar serviço.'
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
                'nome' => 'required',
                'valor_padrao' => 'nullable|numeric',
                'duracao_padrao' => 'nullable|string',
                'ativo' => 'nullable|boolean',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }

            $servico = Servico::where('uid', $uid)->first();
            if (!$servico) {
                return response()->json([
                    'message' => 'Serviço não encontrado.'
                ], 404);
            }

            $servico->nome = $request->nome;
            $servico->valor_padrao = $request->valor_padrao;
            $servico->duracao_padrao = $request->duracao_padrao;
            $servico->ativo = $request->ativo;
            $servico->save();

            return response()->json($servico);
        } catch (\Throwable $th) {
            Log::error('ServicoController::update - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao atualizar serviço.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateAtivo(Request $request, string $uid) {
        try {
            $servico = Servico::where('uid', $uid)->first();
            if (!$servico) {
                return response()->json([
                    'message' => 'Serviço nao encontrado.'
                ], 404);
            }
            $servico->ativo = $request->ativo??true;
            $servico->save();
            return response()->json($servico);
        } catch (\Throwable $th) {
            Log::error('ServicoController::updateAtivo - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao atualizar serviço.',
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
            $servico = Servico::where('uid', $uid)->first();
            if (!$servico) {
                return response()->json([
                    'message' => 'Serviço não encontrado.'
                ], 404);
            }
            $servico->delete();
        } catch (\Throwable $th) {
            Log::error('ServicoController::destroy - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao deletar serviço.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
