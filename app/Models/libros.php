<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Libros extends Model
{   
    protected $collection = 'libros'; // Establece el nombre de la colección

    protected $guarded = ['_id', 'título', 'autor_del_libro', 'año_de_publicacion', 'recurso_digital', 'numero_de_paginas', 'editorial'];

    // Define los tipos de datos de los campos de manera estática
    public static $fieldTypes = [
            'título' => 'string', 'autor_del_libro' => 'string', 'año_de_publicacion' => 'number', 'recurso_digital' => 'file', 'numero_de_paginas' => 'number', 'editorial' => 'string',
        ];

    // Define alias para los campos
    public static $fieldAliases = [
            'título' => 'Título', 'autor_del_libro' => 'Autor Del Libro', 'año_de_publicacion' => 'Año De Publicacion', 'recurso_digital' => 'Recurso Digital', 'numero_de_paginas' => 'Numero De Paginas', 'editorial' => 'Editorial',
        ];

    // Define los campos obligatorios
    public static $requiredFields = ['título', 'autor_del_libro', 'recurso_digital', 'editorial'];
}