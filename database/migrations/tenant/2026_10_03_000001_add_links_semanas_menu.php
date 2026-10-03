<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adiciona o menu "Links JW (Semanas)" e libera permissões do CRUD links-semanas (tenant).
 */
return new class extends Migration
{
    protected $url = 'links-semanas';

    public function up(): void
    {
        $exists = DB::table('menus')->where('url', $this->url)->exists();
        if (!$exists) {
            DB::table('menus')->insert([
                'categoria' => '',
                'description' => 'Links JW (Semanas)',
                'icon' => 'fas fa-link',
                'actived' => true,
                'url' => $this->url,
                'route' => 'links-semanas.index',
                'pai' => 'config',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permissions = DB::table('permissions')->get();
        foreach ($permissions as $perm) {
            $arr = json_decode($perm->id_menu ?? '', true);
            if (!is_array($arr)) {
                continue;
            }
            // Só libera para perfis que já acessam "documentos" (Master/Admin),
            // evitando liberar para perfis restritos.
            if (!isset($arr['ler']['documentos']) && !isset($arr['ler']['qoptions'])) {
                continue;
            }
            $changed = false;
            foreach (['ler', 'ler_arquivos', 'create', 'update', 'delete'] as $acao) {
                if (isset($arr[$acao]) && is_array($arr[$acao]) && !isset($arr[$acao][$this->url])) {
                    $arr[$acao][$this->url] = 's';
                    $changed = true;
                }
            }
            if ($changed) {
                DB::table('permissions')->where('id', $perm->id)->update([
                    'id_menu' => json_encode($arr),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('menus')->where('url', $this->url)->delete();

        $permissions = DB::table('permissions')->get();
        foreach ($permissions as $perm) {
            $arr = json_decode($perm->id_menu ?? '', true);
            if (!is_array($arr)) {
                continue;
            }
            $changed = false;
            foreach (['ler', 'ler_arquivos', 'create', 'update', 'delete'] as $acao) {
                if (isset($arr[$acao][$this->url])) {
                    unset($arr[$acao][$this->url]);
                    $changed = true;
                }
            }
            if ($changed) {
                DB::table('permissions')->where('id', $perm->id)->update([
                    'id_menu' => json_encode($arr),
                ]);
            }
        }
    }
};
