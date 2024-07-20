<?php

namespace App\Console\Commands;

use App\Models\congregacao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TennancyCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compara para criar um novo tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $tenant = congregacao::create([
            'name' => $name,
            'database' => "{$name}_db",
            'username' => "{$name}_user",
            'password' => bcrypt('secret'),
        ]);

        // Crie o banco de dados e execute migrações
        DB::statement("CREATE DATABASE `{$tenant->database}`");
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
        ]);

        $this->info("Tenant {$name} created successfully.");
    }
}
