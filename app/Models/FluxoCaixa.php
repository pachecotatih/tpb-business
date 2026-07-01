<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class FluxoCaixa extends Model
{
    use HasFactory;

    protected $table = 'fluxo_caixa';

    protected $fillable = [
        'uid',
        'descricao',
        'valor',
        'tipo_movimentacao',
        'forma_pagamento',
        'data_vencimento',
        'data_pagamento',
        'pago',
        'observacao',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (FluxoCaixa $fluxoCaixa) {
            $fluxoCaixa->uid = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
