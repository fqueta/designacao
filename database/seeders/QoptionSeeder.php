<?php

namespace Database\Seeders;

use App\Qlib\Qlib;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QoptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('qoptions')->insert([
            [
                'nome'=>'Sessoes das designação',
                'url'=>'sessoes_designacao',
                'valor'=>Qlib::lib_array_json([
                    'inicio'=>['label'=>'Início','color'=>'color1'],
                    'tesouros'=>['label'=>'Tesouros da palavra de Deus','color'=>'color2'],
                    'ministério'=>['label'=>'Faça seu melhor no ministério','color'=>'color3'],
                    'vida'=>['label'=>'Nossa vida cristã','color'=>'color4'],
                    'final'=>['label'=>'Partes mecânicas','color'=>'color5'],
                ]),
            ]
        ]);
    }
}
