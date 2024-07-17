<?php

namespace App\Console\Commands;

use App\Models\congregacao;
use App\Models\empresas;
use App\Qlib\Qlib;
use Database\Seeders\tagSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class TenancySeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:seed {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para realizar a seed no banco de dados dos tenant';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = $this->argument('user');
        $tenancy = congregacao::where('usuario',$user)->firstOrFail();
        $arr_t = $tenancy->toArray();
        $connection = 'tenant';
        if(isset($arr_t['config']) && Qlib::isJson($arr_t['config'])){
            $arr_config = Qlib::lib_json_array($arr_t['config']);
            Qlib::selectDefaultConnection($connection,$arr_config);
        }else{
            return 0;
        }
        // // Run the migrations

        $currentConnection = Config::get('database.default');
        $db = Config::get('database');
        // dump($db);
        $this->info("Semeando banco de dados  do usuario: $user na conexão $currentConnection ");

        // Artisan::call('migrate', [
        //     '--database' => $connection,
        //     '--path' => 'database/migrations/'.$connection,
        // ]);
        //seed
        $arrSeed = [
            // PermissionSeeder::class,
            // UserSeederTenancy::class,
            // MenuTenancySeeder::clas,
            // QoptionSeeder ::class,
            ['path' => 'database/migrations/tenant/2022_02_15_144917_create_users_table','seed'=>UserSeeder::class],
            ['path' => 'database/migrations/tenant/2022_03_15_150747_create_tags_table','seed'=>tagSeeder::class]
        ];
        foreach ($arrSeed as $key => $v) {
            # code...
            // dump($v);
            Artisan::call('migrate:refresh', [
                '--path' => $v['path'],
            ]);
            Artisan::call('db:seed', [
                '--class' => $v['seed'],
            ]);
        }
        // Artisan::call('db:seed', [
        //     '--class' => UserSeeder::class,
        // ]);
        Qlib::selectDefaultConnection('mysql');
        $this->info(Artisan::output());

        return 0;
    }
}
