<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class congregacao extends Model
{
    use HasFactory;
    protected $table = 'congregacoes';
    protected $connection = 'mysql';
    /**
     * com esse metodo eu posso usar assim
     * ret = congregacao:: find($id)->connect();
     */
    public function connect() {
        $this->reconnect($this);
        return $this;
    }
}
