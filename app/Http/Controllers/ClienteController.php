<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
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
            $clientes = Cliente::where('user_id', $user->id)->orderByDesc('created_at')->get();
            return response()->json($clientes);
        } catch (\Exception $e) {
            Log::error('ClienteController::index - ' . $e->getMessage(). ' - ' . $e->getCode(). ' - ' . $e->getFile(). ' - ' . $e->getLine());
            return response()->json(['message' => 'Erro ao buscar clientes: ' . $e->getMessage()], 500);
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
                'telefone'=>'nullable',
                'email'=>'nullable|email',
            ]);
            if ($validation->fails()) {
               return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }
            $user = User::where('uid', $request->header('user'))->first();
            if(!$user) {
                return response()->json(['message' => 'Usuário não encontrado'], 404);
            }
            $cliente = new Cliente();
            $cliente->nome = $request->nome;
            $cliente->telefone = $request->telefone;
            $cliente->email = $request->email;
            $cliente->tipo = $request->tipo ?? 'PF';
            $cliente->endereco = $request->endereco;
            $cliente->data_nascimento = $request->data_nascimento;
            $cliente->documento = $request->documento;
            $cliente->observacao = $request->observacao;
            $cliente->user_id = $user->id;

            $cliente->save();
            return response()->json($cliente);

        } catch (\Throwable $th) {
            Log::error('ClienteController::store - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json(['message' => 'Erro ao validar cliente: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uid)
    {
        try {
            $cliente = Cliente::where('uid', $uid)->first();
            if(!$cliente) {
                return response()->json(['message' => 'Cliente não encontrado'], 404);
            }
            return response()->json($cliente);
        } catch (\Throwable $th) {
            Log::error('ClienteController::show - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json(['message' => 'Erro ao buscar cliente: ' . $th->getMessage()], 500);
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
        try{
            $validation = Validator::make($request->all(), [
                'nome' => 'required',
                'telefone'=>'nullable',
                'email'=>'nullable|email'
            ]);
            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validation->errors()
                ], 422);
            }
            $cliente = Cliente::where('uid', $uid)->first();
            if(!$cliente) {
                return response()->json([
                    'message' => 'Cliente não encontrado'
                ], 404);
            }
            $cliente->nome = $request->nome;
            $cliente->telefone = $request->telefone;
            $cliente->email = $request->email;
            $cliente->tipo = $request->tipo ?? 'PF';
            $cliente->endereco = $request->endereco;
            $cliente->data_nascimento = $request->data_nascimento;
            $cliente->documento = $request->documento;
            $cliente->observacao = $request->observacao;
            $cliente->save();
            return response()->json($cliente);
        } catch (\Throwable $th) {
            Log::error('ClienteController::update - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json(['message' => 'Erro ao atualizar cliente: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uid)
    {
        try {
            $cliente = Cliente::where('uid', $uid)->first();
            if(!$cliente) {
                return response()->json([
                    'message' => 'Cliente não encontrado'
                ], 404);
            }
            $cliente->delete();
            return response()->json(['message' => 'Cliente excluido com sucesso']);
        } catch (\Throwable $th) {
            Log::error('ClienteController::destroy - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json(['message' => 'Erro ao excluir cliente: ' . $th->getMessage()], 500);
        }
    }
}
