<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Fotografias extends Model
{   
    protected $collection = 'fotografias'; // Establece el nombre de la colección

    protected $guarded = ['_id', 'título', 'autor', 'año_de_publicación', 'recurso_digital', ];

    // Define los tipos de datos de los campos de manera estática
    public static $fieldTypes = [
        'título' => 'string', 'autor' => 'string', 'año_de_publicación' => 'number', 'recurso_digital' => 'file', 
    ];

    // Define alias para los campos
    public static $fieldAliases = [
        'título' => 'Título', 'autor' => 'Autor', 'año_de_publicación' => 'Año De Publicación', 'recurso_digital' => 'Recurso Digital', 
    ];

    // Define los campos obligatorios
    public static $requiredFields = [
        'título', 'autor', 'recurso_digital', 
    ];
}