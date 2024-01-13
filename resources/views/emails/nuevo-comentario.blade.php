<!-- resources/views/emails/nuevo-comentario.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h2>Nuevo Comentario</h2>

    <p>¡Hola!</p>

    <p>Se ha creado un nuevo comentario. Aquí están los detalles:</p>

    <strong>Usuario:</strong> {{ $comentario->usuario->nombre }} {{ $comentario->usuario->apellido_paterno }} {{ $comentario->usuario->apellido_materno }}

    <strong>Contenido del Comentario:</strong> {{ $comentario->contenido }}


    <p>¡Gracias!</p>
</body>
</html>
