<?php

namespace App\Console\Commands;

use App\Models\congregacao;
use App\Models\empresas;
use App\Qlib\Qlib;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
        }
        // // Run the migrations
        $this->info("Semeando banco de dados do usuario: $user");

        // Artisan::call('migrate', [
        //     '--database' => $connection,
        //     '--path' => 'database/migrations/'.$connection,
        // ]);
        //seed
        $arrSeed = [
            // PermissionSeeder::class,
            // UserSeederTenancy::class,
            // MenuTenancySeeder::class,
            // QoptionSeeder ::class,
             UserSeeder::class,
            // tagSeeder::class,
        ];
        foreach ($arrSeed as $key => $value) {
            # code...
            $ret['2'][$key] = Artisan::call('db:seed', [
                '--class' => $value,
                // '--class' => UserSeeder::class,
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
