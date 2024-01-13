<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Mapas extends Model
{   
    protected $collection = 'mapas'; // Establece el nombre de la colección

    protected $guarded = ['_id', 'nombre', 'titulo', 'año', 'escala', ];

    // Define los tipos de datos de los campos de manera estática
    public static $fieldTypes = [
        'nombre' => 'string', 'titulo' => 'string', 'año' => 'date', 'escala' => 'number', 
    ];

    // Define alias para los campos
    public static $fieldAliases = [
        'nombre' => 'Nombre', 'titulo' => 'Titulo', 'año' => 'Año', 'escala' => 'Escala', 
    ];

    // Define los campos obligatorios
    public static $requiredFields = [
        'nombre', 'titulo', 
    ];
}