<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Comentario;

class NuevoComentario extends Mailable
{
    use Queueable, SerializesModels;
    public $comentario;


    /** 
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Comentario $comentario)
    {
        $this->comentario = $comentario;

    }

    public function build()
    {
        return $this->from('jidd.2201@hotmail.com', 'Nombre del Remitente')
                    ->view('emails.nuevo-comentario');
    }
}
