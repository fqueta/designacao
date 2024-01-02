<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menus')->insert([
            [
                'categoria'=>'',
                'description'=>'Painel',
                'icon'=>'fa fa-tachometer-alt',
                'actived'=>true,
                'url'=>'painel',
                'route'=>'home',
                'pai'=>''
            ],
            [
                'categoria'=>'CADASTROS',
                'description'=>'Estudantes',
                'icon'=>'fas fa-user',
                'actived'=>true,
                'url'=>'publicadores',
                'route'=>'publicadores.index',
                'pai'=>''
            ],
            [
                'categoria'=>'',
                'description'=>'Programação',
                'icon'=>'fas fa-file',
                'actived'=>true,
                'url'=>'programa',
                'route'=>'programa.index',
                'pai'=>''
            ],
            [
                'categoria'=>'DESIGNAÇÕES',
                'description'=>'Histórico de programas',
                'icon'=>'fas fa-file',
                'actived'=>true,
                'url'=>'programa-historico',
                'route'=>'programa.index',
                'pai'=>''
            ],
            [
                'categoria'=>'',
                'description'=>'Novo programa',
                'icon'=>'fas fa-file',
                'actived'=>true,
                'url'=>'programa-novo',
                'route'=>'programa.create',
                'pai'=>''
            ],
            [
                'categoria'=>'RELATORIOS',
                'description'=>'Acessos',
                'icon'=>'fas fa-calendar-alt',
                'actived'=>true,
                'url'=>'relatorios_acessos',
                'route'=>'relatorios.acessos',
                'pai'=>'relatorios'
            ],
            /*[
                'categoria'=>'',
                'description'=>'Listagem de Ocupantes',
                'icon'=>'fa fa-chart-bar',
                'actived'=>true,
                'url'=>'relatorios_evolucao',
                'route'=>'relatorios.evolucao',
                'pai'=>'relatorios'
            ],*/
            [
                'categoria'=>'SISTEMA',
                'description'=>'Configurações',
                'icon'=>'fas fa-cogs',
                'actived'=>true,
                'url'=>'config',
                'route'=>'sistema.config',
                'pai'=>''
            ],
            [
                'categoria'=>'',
                'description'=>'Documentos',
                'icon'=>'fas fa-file-word',
                'actived'=>true,
                'url'=>'documentos',
                'route'=>'documentos.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Perfil',
                'icon'=>'fas fa-user',
                'actived'=>true,
                'url'=>'sistema',
                'route'=>'sistema.perfil',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Usuários',
                'icon'=>'fas fa-users',
                'actived'=>true,
                'url'=>'users',
                'route'=>'users.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Permissões',
                'icon'=>'far fa-list-alt ',
                'actived'=>true,
                'url'=>'permissions',
                'route'=>'permissions.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Listas do sistema (Tags)',
                'icon'=>'fas fa-list',
                'actived'=>true,
                'url'=>'tags',
                'route'=>'tags.index',
                'pai'=>'config'
            ],
            [
                'categoria'=>'',
                'description'=>'Avançado (Dev)',
                'icon'=>'fas fa-user',
                'actived'=>true,
                'url'=>'qoptions',
                'route'=>'qoptions.index',
                'pai'=>'config'
            ],
        ]);
    }
}
