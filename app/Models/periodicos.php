<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Periodicos extends Model
{   
    protected $collection = 'periodicos'; // Establece el nombre de la colección

    protected $guarded = ['_id', 'título', 'autor', 'año', 'recurso_digital', 'editorial'];

    // Define los tipos de datos de los campos de manera estática
    public static $fieldTypes = [
            'título' => 'string', 'autor' => 'string', 'año' => 'number', 'recurso_digital' => 'file', 'editorial' => 'string',
        ];

    // Define alias para los campos
    public static $fieldAliases = [
            'título' => 'Título', 'autor' => 'Autor', 'año' => 'Año', 'recurso_digital' => 'Recurso Digital', 'editorial' => 'Editorial',
        ];

    // Define los campos obligatorios
    public static $requiredFields = [''];
}