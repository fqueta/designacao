<?php

namespace Database\Seeders;

use App\Qlib\Qlib;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CongregacoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('congregacoes')->insert([
            [
                'nome'=>'Bela Aurora',
                'usuario'=>'ba',
                'config'=>Qlib::lib_array_json([
                    'name'=>'maisaqu_des',
                    'user'=>'maisaqu_admin',
                    'pass'=>'maisaqui1@234',
                ]),
                'ativo'=>'s',
            ],
            [
                'nome'=>'Jardim Esplanada',
                'usuario'=>'je',
                'config'=>Qlib::lib_array_json([
                    'name'=>'maisaqu_desje',
                    'user'=>'maisaqu_admin',
                    'pass'=>'maisaqui1@234',
                ]),
                'ativo'=>'s',
            ],
        ]);
    }
}
