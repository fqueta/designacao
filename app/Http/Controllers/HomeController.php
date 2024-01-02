<?php

namespace App\Http\Controllers;

use App\Http\Controllers\admin\EventController;
use App\Models\User;
use App\Models\relatorio;
use App\Models\Assistencia;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Qlib\Qlib;
//use Spatie\Permission\Models\Role;
//use Spatie\Permission\Models\Permission;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $user;
    public $tab;
    public function __construct(User $user)
    {
        $this->middleware('auth');
        $this->user = $user;
        $this->tab = 'home';
        $this->title = 'Dashboard';
        $this->routa = 'painel';
    }

    public function teste(){
      //$dados = $request->all();
      //var_dump($dados);
      return view('teste');
    }
    public function upload(Request $request){
      $dados = $request->all();
      var_dump($dados);
    }


    public function index()
    {
        $this->authorize('ler', $this->routa);
        // $controlerFamilias = new FamiliaController(Auth::user());
        // $controlerMapas = new MapasController(Auth::user());
        // $dadosFamilias = $controlerFamilias->queryFamilias();
        // $id_quadra_home = Qlib::qoption('id_quadra_home')?Qlib::qoption('id_quadra_home'):@$_GET['id_qh'];
        // if($id_quadra_home){
        //     $dadosMp = $controlerMapas->queryQuadras($id_quadra_home);
        // }else{
        //     $dadosMp = false;
        // }
        //REGISTRAR EVENTOS
        // (new EventController)->listarEvent(['tab'=>$this->tab,'this'=>$this]);
        // $totalDecretos = Post::where('post_type','=','decreto')->count();
        $config = [
            // 'c_familias'=>$dadosFamilias,
            // 'mapa'=>$dadosMp,
            // 'totalDecretos'=>$totalDecretos,
        ];
        return view('home',[
            'config'=>$config,
        ]);
    }

}
