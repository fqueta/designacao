<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class tagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            ['nome'=>'Tipos de desiganção','obs'=>'tidpos de desiganção.','ordem'=>1],
            [
                'nome'=>'Presidencia',
                'pai'=>1,
                'ordem'=>1,
                'value'=>'inicio',
                'obs'=>'',
                'config'=>['color'=>'greey','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'especial']
            ],
            [
                'nome'=>'Oração Inicial',
                'pai'=>1,
                'ordem'=>2,
                'value'=>'inicio',
                'obs'=>'',
                'config'=>['color'=>'greey','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'mecanica','local'=>'ministerio']
            ],
            [
                'nome'=>'Tesouros',
                'pai'=>1,
                'ordem'=>3,
                'value'=>'tesouros',
                'obs'=>'Dez minutos. Essa é uma parte de perguntas e respostas sem introdução nem conclusão. Essa parte deve ser feita por um ancião ou servo ministerial qualificado. Ele deve fazer as duas perguntas para a assistência. Além disso, ele decide se é ou não necessário ler os versículos citados. A assistência deve dar comentários de 30 segundos ou menos.',
                'config'=>['color'=>'greey','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'instrucao']
            ],
            [
                'nome'=>'Joias espirituais',
                'pai'=>1,
                'value'=>'tesouros',
                'ordem'=>4,
                'obs'=>'Dez minutos. Essa é uma parte de perguntas e respostas sem introdução nem conclusão. Essa parte deve ser feita por um ancião ou servo ministerial qualificado. Ele deve fazer as duas perguntas para a assistência. Além disso, ele decide se é ou não necessário ler os versículos citados. A assistência deve dar comentários de 30 segundos ou menos.',
                'config'=>['color'=>'greey','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'instrucao']
            ],
            [
                'nome'=>'Leitura da Bíblia',
                'pai'=>1,
                'ordem'=>5,
                'value'=>'mininisterio',
                'obs'=>'Quatro minutos. Essa é uma parte de estudante e deve ser feita por um irmão. O estudante deve ler a matéria designada sem fazer introdução nem conclusão. O presidente da reunião deve ajudar os estudantes a ler de modo exato, compreensível, fluente, com ênfase de acordo com o sentido do texto, com modulação, pausas apropriadas e naturalidade. Alguns trechos designados para leitura são maiores que outros; por isso, o superintendente da Reunião Vida e Ministério deve levar em conta as habilidades dos estudantes ao fazer as designações.',
                'config'=>['color'=>'greey','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'estudante','genero'=>['m']]
            ],
            [
                'nome'=>'Vídeos de “Conversas sobre a Bíblia”',
                'pai'=>1,
                'ordem'=>6,
                'obs'=>'Vídeos de sugestões de conversas serão exibidos e considerados com a assistência regularmente. Esses vídeos serão sobre a primeira conversa e uma revisita. Essa parte será feita pelo presidente da Reunião Vida e Ministério.',
                'config'=>['color'=>'greey','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'estudante','genero'=>['m']]
            ],
            [
                'nome'=>'Consideração',
                'pai'=>1,
                'ordem'=>7,
                'obs'=>'Vídeos de sugestões de conversas serão exibidos e considerados com a assistência regularmente. Esses vídeos serão sobre a primeira conversa e uma revisita. Essa parte será feita pelo presidente da Reunião Vida e Ministério.',
                'config'=>['color'=>'greey','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'estudante','genero'=>['m']]
            ],
            [
                'nome'=>'Iniciando conversas',
                'pai'=>1,
                'ordem'=>8,
                'obs'=>'Essa designação de estudante pode ser feita por um irmão ou uma irmã. O ajudante deve ser alguém do mesmo sexo que o estudante ou um membro da família do estudante. O estudante e o ajudante podem ficar sentados ou em pé. O estudante deve começar com um cumprimento apropriado para a região. O assunto da sugestão de conversa da Apostila da Reunião Vida e Ministério deve ser usado como base.',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'estudante','genero'=>['m','f']]
            ],
            [
                'nome'=>'Cultivando o interesse',
                'pai'=>1,
                'ordem'=>9,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'estudante','genero'=>['m','f']]
            ],
            [
                'nome'=>'Fazendo discipulos',
                'pai'=>1,
                'ordem'=>10,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'estudante','genero'=>['m','f'],'local'=>'ministerio']
            ],
            [
                'nome'=>'Explicando suas crenças',
                'pai'=>1,
                'ordem'=>10,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'estudante','genero'=>['m','f']]
            ],
            [
                'nome'=>'Discurso',
                'pai'=>1,
                'ordem'=>11,
                'obs'=>'Essa designação de estudante pode ser feita por um irmão ou uma irmã. O ajudante não deve ser alguém do sexo oposto. (km 5/97 p. 3) O estudante e seu ajudante podem ficar sentados ou em pé. O estudante deve demonstrar o que dizer quando se revisita alguém que mostrou interesse durante a primeira conversa. O assunto da sugestão de conversa da Apostila da Reunião Vida e Ministério deve ser usado como base.',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'estudante','genero'=>['m']]
            ],
            [
                'nome'=>'Vida 1',
                'pai'=>1,
                'ordem'=>12,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'instrucao']
            ],
            [
                'nome'=>'Vida 2',
                'pai'=>1,
                'ordem'=>13,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'instrucao']
            ],
            [
                'nome'=>'Necessidades locais',
                'pai'=>1,
                'ordem'=>14,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'especial']
            ],
            [
                'nome'=>'Estudo bíblico',
                'pai'=>1,
                'ordem'=>15,
                'obs'=>'Essa designação de estudante deve ser feita por um irmão. Ele deve fazer um discurso para a congregação.',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'especial']
            ],
            [
                'nome'=>'Leitor',
                'pai'=>1,
                'ordem'=>16,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'mecanica']
            ],
            [
                'nome'=>'Oração final',
                'pai'=>1,
                'ordem'=>17,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'mecanica','local'=>'ministerio']
            ],
            [
                'nome'=>'Microfone volante',
                'pai'=>1,
                'ordem'=>18,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'mecanica','local'=>'final']
            ],
            [
                'nome'=>'Microfone volante2',
                'pai'=>1,
                'ordem'=>19,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'mecanica','local'=>'final']
            ],
            [
                'nome'=>'Indicador de auditório',
                'pai'=>1,
                'ordem'=>20,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'mecanica','local'=>'final']
            ],
            [
                'nome'=>'Indicador externo',
                'pai'=>1,
                'ordem'=>21,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'mecanica','local'=>'final']
            ],
            [
                'nome'=>'Palco',
                'pai'=>1,
                'ordem'=>22,
                'obs'=>'',
                'config'=>['color'=>'warning','post_type'=>'meio-semana','icon'=>'fa fa-times','t_p'=>'mecanica','local'=>'final']
            ],
            [
                'nome'=>'Presidente',
                'pai'=>1,
                'ordem'=>23,
                'obs'=>'',
                'config'=>['color'=>'info','post_type'=>'fim-semana','icon'=>'fa fa-times','t_p'=>'instrucao','local'=>'incio']
            ],
            [
                'nome'=>'Leitor',
                'pai'=>1,
                'ordem'=>24,
                'obs'=>'',
                'config'=>['color'=>'info','post_type'=>'fim-semana','icon'=>'fa fa-times','t_p'=>'mecanica','local'=>'incio']
            ],
            [
                'nome'=>'Orador',
                'pai'=>1,
                'ordem'=>25,
                'obs'=>'',
                'config'=>['color'=>'info','post_type'=>'fim-semana','icon'=>'fa fa-times','t_p'=>'instrucao','local'=>'incio']
            ],
        ];

        foreach ($arr as $key => $value) {
            $d = $value;
            $d['token']=uniqid();
            Tag::create($d);
        }
    }
}
