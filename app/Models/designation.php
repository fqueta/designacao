<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class designation extends Model
{
    use HasFactory;
    protected $casts = [
        'config' => 'array',
    ];

    protected $fillable = [
        'token',
        'data',
        'numero',
        'id_designado',
        'id_ajudante',
        'orador_visitante',
        'id_designacao',
        'post_type',
        'sessao',
        'ativo',
        'ordem',
        'autor',
        'obs',
        'config',
        'obs',
        'excluido',
        'reg_excluido',
        'deletado',
        'reg_deletado'
    ];
}
