<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Agendamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'data_inicio',
        'data_fim',
        'status',
        'valor_total',
        'user_id',
        'cliente_id',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (Agendamento $agendamento) {
            $agendamento->uid = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function servicos()
    {
        return $this->belongsToMany(Servico::class, 'agendamentos_servicos')->withPivot('valor_servico', 'duracao_servico')->withTimestamps();
    }
}
