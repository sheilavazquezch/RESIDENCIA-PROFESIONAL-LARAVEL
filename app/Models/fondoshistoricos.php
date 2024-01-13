<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Fondoshistoricos extends Model
{   
    protected $collection = 'fondoshistoricos'; // Establece el nombre de la colección

    protected $guarded = ['_id', 'fondo', 'seccion', 'serie', 'sub-serie', 'expediente', 'documento'];

    // Define los tipos de datos de los campos de manera estática
    public static $fieldTypes = [
            'fondo' => 'string', 'seccion' => 'string', 'serie' => 'string', 'sub-serie' => 'string', 'expediente' => 'string', 'documento' => 'file',
        ];

    // Define alias para los campos
    public static $fieldAliases = [
            'fondo' => 'Fondo', 'seccion' => 'Seccion', 'serie' => 'Serie', 'sub-serie' => 'Sub-Serie', 'expediente' => 'Expediente', 'documento' => 'Documento',
        ];

    // Define los campos obligatorios
    public static $requiredFields = ['fondo', 'seccion', 'serie', 'expediente', 'documento'];
}