<?php

use App\Http\Controllers\admin\designaController;
use App\Http\Controllers\admin\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\admin\UserPermissions;
use App\Http\Controllers\GerenciarGrupo;
use App\Http\Controllers\GerenciarUsuarios;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\BairroController;
use App\Http\Controllers\TesteController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\EtapaController;
use App\Http\Controllers\EscolaridadeController;
use App\Http\Controllers\EstadocivilController;
use App\Http\Controllers\LotesController;
use App\Http\Controllers\RelatoriosController;
use App\Http\Controllers\MapasController;
use App\Http\Controllers\PublicadoresController;
use App\Qlib\Qlib;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('/ajax')->group(function(){
    Route::post('/desigancao-remove',[designaController::class,'removeDesignacao'])->name('ajax.designacao.remove');
});
Route::prefix('users')->group(function(){
    Route::get('/',[UserController::class,'index'])->name('users.index');

    Route::get('/ajax',[UserController::class,'paginacaoAjax'])->name('users.ajax');
    Route::get('/lista.ajax',function(){
        return view('users.index_ajax');
    });

    Route::get('/create',[UserController::class,'create'])->name('users.create');
    Route::post('/',[UserController::class,'store'])->name('users.store');
    Route::get('/{id}/show',[UserController::class,'show'])->where('id', '[0-9]+')->name('users.show');
    Route::get('/{id}/edit',[UserController::class,'edit'])->where('id', '[0-9]+')->name('users.edit');
    Route::put('/{id}',[UserController::class,'update'])->where('id', '[0-9]+')->name('users.update');
    Route::delete('/{id}',[UserController::class,'destroy'])->where('id', '[0-9]+')->name('users.destroy');
});
Route::prefix('relatorios')->group(function(){
    Route::get('/',[RelatoriosController::class,'index'])->name('relatorios.index');
    Route::get('/social',[RelatoriosController::class,'realidadeSocial'])->name('relatorios.social');
    Route::get('/acessos',[EventController::class,'listAcessos'])->name('relatorios.acessos');
    Route::get('export/filter', [RelatoriosController::class, 'exportFilter'])->name('relatorios.export_filter');
    //Route::post('/',[RelatoriosController::class,'store'])->name('relatorios.store');
    //Route::get('/{id}/show',[RelatoriosController::class,'show'])->name('relatorios.show');
    //Route::get('/{id}/edit',[RelatoriosController::class,'edit'])->name('relatorios.edit');
    //Route::put('/{id}',[RelatoriosController::class,'update'])->where('id', '[0-9]+')->name('relatorios.update');
    //Route::post('/{id}',[RelatoriosController::class,'update'])->where('id', '[0-9]+')->name('relatorios.update-ajax');
    //Route::delete('/{id}',[RelatoriosController::class,'destroy'])->where('id', '[0-9]+')->name('relatorios.destroy');
});
Route::prefix('sistema')->group(function(){
    Route::get('/pefil',[UserController::class,'perfilShow'])->name('sistema.perfil');
    Route::get('/perfil/edit',[UserController::class,'perfilEdit'])->name('sistema.perfil.edit');
    Route::post('/perfil/store',[UserController::class,'perfilStore'])->name('sistema.perfil.store');
    Route::get('/config',[EtapaController::class,'config'])->name('sistema.config');
    Route::post('/{id}',[EtapaController::class,'update'])->where('id', '[0-9]+')->name('sistema.update-ajax');
});
Route::prefix('uploads')->group(function(){
    Route::get('/',[uploadController::class,'index'])->name('uploads.index');
    Route::get('/create',[UploadController::class,'create'])->name('uploads.create');
    Route::post('/',[UploadController::class,'store'])->name('uploads.store');
    Route::get('/{id}/show',[UploadController::class,'show'])->name('uploads.show');
    Route::get('/{id}/edit',[UploadController::class,'edit'])->name('uploads.edit');
    Route::put('/{id}',[UploadController::class,'update'])->where('id', '[0-9]+')->name('uploads.update');
    Route::post('/{id}',[UploadController::class,'update'])->where('id', '[0-9]+')->name('uploads.update-ajax');
    Route::post('/{id}',[UploadController::class,'destroy'])->where('id', '[0-9]+')->name('uploads.destroy');
    Route::get('export/all', [UploadController::class, 'exportAll'])->name('uploads.export_all');
    Route::get('export/filter', [UploadController::class, 'exportFilter'])->name('uploads.export_filter');
});
Route::fallback(function () {
    return view('erro404');
});
Route::get('menu/{id}', [App\Http\Controllers\HomeController::class, 'menu'])->name('menu');
Route::prefix('teste')->group(function(){
    Route::get('/',[App\Http\Controllers\TesteController::class,'index'])->name('teste');
    Route::get('/ajax',[App\Http\Controllers\TesteController::class,'ajax'])->name('teste.ajax');
});

//Route::post('/upload',[App\Http\Controllers\UploadFile::class,'upload'])->name('teste.upload');

// Route::resource('beneficiarios','\App\Http\Controllers\BeneficiariosController',['parameters' => [
// 'beneficiarios' => 'id'
// ]]);
// Route::resource('lotes','\App\Http\Controllers\LotesController',['parameters' => [
// 'lotes' => 'id'
// ]]);
// Route::get('/lotes/lista-ocupantes/{lotes}',[LotesController::class,'listagemOcupantes'])->name('lotes.ocupantes');
// Route::get('/lotes/ficha-ocupantes/{lote}/{familia}',[LotesController::class,'FichaOcupantes'])->name('lotes.ficha_ocupantes');
// Route::resource('quadras','\App\Http\Controllers\QuadrasController',['parameters' => [
// 'quadras' => 'id'
// ]]);
Route::resource('documentos','\App\Http\Controllers\DocumentosController',['parameters' => [
    'documentos' => 'id'
]]);
Route::resource('qoptions','\App\Http\Controllers\admin\QoptionsController',['parameters' => [
    'qoptions' => 'id'
]]);
Route::resource('tags','\App\Http\Controllers\admin\TagsController',['parameters' => [
    'tags' => 'id'
]]);
Route::resource('permissions','\App\Http\Controllers\admin\UserPermissions',['parameters' => [
    'permissions' => 'id'
]]);
Route::resource('programa','\App\Http\Controllers\admin\PostController',['parameters' => [
    'programa' => 'id'
]]);


Auth::routes();

Route::get('/',function(){
  return redirect()->route('login');
});
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/transparencia', [App\Http\Controllers\HomeController::class, 'transparencia'])->name('transparencia');

Route::get('envio-mails',function(){
    $user = new stdClass();
    $user->name = 'Fernando Queta';
    $user->email = 'ferqueta@yahoo.com.br';
    // return new \App\Mail\dataBrasil($user);

    $enviar = Mail::send(new \App\Mail\dataBrasil($user));
    return $enviar;
});
Route::get('/suspenso',[UserController::class,'suspenso'])->name('cobranca.suspenso');
Route::prefix('cobranca')->group(function(){
    Route::get('/fechar',[UserController::class,'pararAlertaFaturaVencida'])->name('alerta.cobranca.fechar');
});
Route::prefix('publicadores')->group(function(){
    Route::get('/',[PublicadoresController::class,'index'])->name('publicadores.index');
    Route::get('/create',[PublicadoresController::class,'create'])->name('publicadores.create');
    Route::post('/',[PublicadoresController::class,'store'])->name('publicadores.store');
    Route::get('/{id}/edit',[PublicadoresController::class,'edit'])->where('id', '[0-9]+')->name('publicadores.edit');
    Route::put('/{id}',[PublicadoresController::class,'update'])->where('id', '[0-9]+')->name('publicadores.update');
    Route::delete('/{id}',[PublicadoresController::class,'destroy'])->where('id', '[0-9]+')->name('publicadores.destroy');

    Route::get('/{id}/cartao',[PublicadoresController::class,'cartao'])->name('publicadores.cartao');
    Route::get('/cards',[PublicadoresController::class,'cards'])->name('publicadores.cards');
});

