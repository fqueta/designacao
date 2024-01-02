<?php

namespace App\Mail;

use App\Qlib\Qlib;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use stdClass;

class dataBrasil extends Mailable
{
    use Queueable, SerializesModels;

    private $user;

    public function __construct(stdClass $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Novo teste');
        $this->to($this->user->email,$this->user->name);
        $file = dirname(dirname(dirname(__FILE__))) . '/bk2.html';
        $html_teste = Qlib::carregaArquivo($file);
        // return $this->from('ferqueta@yahoo.com.br')
        //         ->view('mail.test',['email' => $html_teste]);
        return $this->markdown('mail.dataBrasil',[
            'user'=>$this->user,
        ]);
    }
}
