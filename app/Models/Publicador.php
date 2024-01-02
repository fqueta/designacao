<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Publicador extends Model
{
    use HasFactory,Notifiable;
    protected $casts = [
        'config' => 'array',
        'tags' => 'array',
    ];
    protected $fillable = [
        'nome',
        'obs',
        'ativo',
        'data_nasci',
        'data_batismo',
        'tel',
        'config',
        'tags',
        'endereco'
    ];
    protected $table = 'publicadores';
}
