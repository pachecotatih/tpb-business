<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'nome',
        'email',
        'telefone',
        'endereco',
        'data_nascimento',
        'documento',
        'tipo',
        'observacao',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (Cliente $cliente) {
            $cliente->uid = (string) Str::uuid();
        });
    }

}
