<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Servico extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'nome',
        'valor_padrao',
        'duracao_padrao',
        'ativo',
    ];

    protected $with = [];

    public static function boot() {
        parent::boot();
        static::creating(function (Servico $servico) {
            $servico->uid = (string) Str::uuid();
        });
    }

    public function agendamentos()
    {
        return $this->belongsToMany(Agendamento::class, 'agendamentos_servicos')->withPivot('valor_servico', 'duracao_servico')->withTimestamps();
    }
}
