<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class LinkSemana extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'links_semanas';

    public $timestamps = false;

    protected $fillable = [
        'data',
        'link',
    ];
}
