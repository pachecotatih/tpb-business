<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\FluxoCaixa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(Request $request) {
        try {
            $user = User::where('uid', $request->header('user'))->select('id')->first();
            if(!$user) {
                return response()->json(['message' => 'Usuário não encontrado'], 404);
            }
            $fluxo_caixa = FluxoCaixa::where('user_id', $user->id);
            $saldo_hoje = $fluxo_caixa->whereDate('created_at', Carbon::now()->format('Y-m-d'))->sum(function ($item) {
                return $item->tipo_movimentacao === 'saida'
                    ? -$item->valor
                    : $item->valor;
            });
            $entradas_hoje = $fluxo_caixa->whereDate('data_pagamento', Carbon::now()->format('Y-m-d'))->where('tipo_movimentacao', 'entrada')->sum('valor');
            $saidas_hoje = $fluxo_caixa->whereDate('created_at', Carbon::now()->format('Y-m-d'))->where('tipo_movimentacao', 'saida')->sum('valor');
            $agendamentos_hoje = Agendamento::with(['cliente', 'servicos:uid,nome'])->whereDate('data_inicio', Carbon::now()->format('Y-m-d'))->where('status', '!=', 'concluido')->where('user_id', $user->id)->get();
            return response()->json(['saldo_hoje' => $saldo_hoje, 'entradas_hoje' => $entradas_hoje, 'saidas_hoje' => $saidas_hoje, 'agendamentos_hoje' => $agendamentos_hoje]);
        } catch (\Throwable $th) {
            Log::error('HomeController::index - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json(['message' => 'Erro ao buscar serviços: ' . $th->getMessage()], 500);
        }
    }
}
