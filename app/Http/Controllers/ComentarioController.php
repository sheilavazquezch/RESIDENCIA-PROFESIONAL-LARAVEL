<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comentario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevoComentario;
use App\Models\User;
class ComentarioController extends Controller
{
    public function index()
    {
        // Lógica para obtener y mostrar todos los comentarios
    }

        public function store(Request $request)
    {
        // Validar la solicitud
        log::info($request);
        $request->validate([
            'usuario_id' => 'required|exists:users,_id',
            'documento_id' => 'required', // Aquí puedes agregar lógica adicional si es necesario
            'contenido' => 'required',
        ]);

        // Crear el comentario
        $comentario = Comentario::create([
            'usuario_id' => $request->input('usuario_id'),
            'documento_id' => $request->input('documento_id'),
            'contenido' => $request->input('contenido'),
            'estado' => 'pendiente', // Puedes ajustar esto según tus requisitos
        ]);

        /*
        $usuariosConRol = User::whereIn('role', ['Validador', 'Admin'])->get();

        $destinatarios = $usuariosConRol->pluck('email')->toArray();
        Mail::to($destinatarios)->send(new NuevoComentario($comentario));
*/
        // Puedes enviar una respuesta JSON, redirigir a otra página, etc.
        return response()->json(['message' => 'Comentario creado con éxito', 'comentario' => $comentario], 201);
    }



public function comentariosAprobados($documentoId)
{
    $comentariosAprobados = Comentario::with('usuario')
        ->where('documento_id', $documentoId)
        ->where('estado', 'aprobado')
        ->get();

    log::info($comentariosAprobados);
    return response()->json(['comentariosAprobados' => $comentariosAprobados]);
}



    public function aprobarComentario($id)
{
    $comentario = Comentario::find($id);

    if (!$comentario) {
        return response()->json(['message' => 'Comentario no encontrado'], 404);
    }

    $comentario->estado = 'aprobado';
    $comentario->save();

    return response()->json(['message' => 'Comentario aprobado con éxito']);
}



public function denegarComentario($id)
{
    $comentario = Comentario::find($id);

    if (!$comentario) {
        return response()->json(['message' => 'Comentario no encontrado'], 404);
    }

    $comentario->delete();

    return response()->json(['message' => 'Comentario denegado y eliminado con éxito']);
}

public function obtenerComentariosPendientes()
{
    // Obtiene comentarios pendientes con información del usuario y del documento
    $comentariosPendientes = Comentario::with(['usuario'])
        ->where('estado', 'pendiente')
        ->get();
         // Obtener detalles del documento para cada comentario
        // Obtener detalles del documento para cada comentario y agregarlo directamente a la colección
        foreach ($comentariosPendientes as $comentario) {
            $documento = $comentario->obtenerDocumento();

            // Asignar la información del documento como un atributo adicional
            $comentario->setAttribute('documento', $documento);
        }
    log::info($comentariosPendientes);
    return response()->json(['comentariosPendientes' => $comentariosPendientes]);
}


public function obtenerComentariosAprobados()
{
    // Obtiene comentarios aprobados con información del usuario y del documento
    $comentariosAprobados = Comentario::with(['usuario'])
        ->where('estado', 'aprobado')
        ->get();
        foreach ($comentariosAprobados as $comentario) {
            $documento = $comentario->obtenerDocumento();

            // Asignar la información del documento como un atributo adicional
            $comentario->setAttribute('documento', $documento);
        }
    return response()->json(['comentariosAprobados' => $comentariosAprobados]);
}

/*
public function enviar  Correo(Request $request)
    {
        $destinatario = $request->input('destinatario');
        $asunto = $request->input('asunto');
        $contenido = $request->input('contenido');

        Mail::to($contenido, function (Message $message) use ($destinatario, $asunto) {
            $message->to($destinatario)
                    ->subject($asunto);
        });

        return response()->json(['mensaje' => 'Correo enviado correctamente']);
    }
*/
}
