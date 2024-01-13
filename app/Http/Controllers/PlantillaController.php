<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Intervention\Image\Facades\Image;
use MongoDB\Client;
use App\Models\PlantillaPredeterminada;
use ZipArchive;

class PlantillaController extends Controller

{


   
    

    public function get()
{
    $collections = DB::connection('mongodb')->listCollections();

    $plantillas = [];

    foreach ($collections as $collection) {
        $plantillas[] = $collection->getName();
    }

    return response()->json($plantillas);
}

public function getforDocuments()
{
    $collections = DB::connection('mongodb')->listCollections();

    $plantillas = [];

    foreach ($collections as $collection) {
        $plantillas[] = $collection->getName();
    }

    return response()->json($plantillas);
}




    public function delete($plantillaName)
    {
        // Verifica si la colección existe
        $collections = DB::connection('mongodb')->listCollections();
        $collectionExists = false;

        foreach ($collections as $collection) {
            if ($collection->getName() === $plantillaName) {
                $collectionExists = true;
                break;
            }
        }

        if ($collectionExists) {
            // Elimina la colección en MongoDB
            DB::connection('mongodb')->getCollection($plantillaName)->drop();

            // Elimina el modelo si existe
            $modelPath = app_path("Models/{$plantillaName}.php");
            if (file_exists($modelPath)) {
                unlink($modelPath);
            }

            return response()->json(['message' => 'Colección, modelo y migraciones eliminados con éxito'], 200);
        } else {
            return response()->json(['error' => "La colección '{$plantillaName}' no existe."], 404);
        }
    }

    

    public function create(Request $request)
    {
        $request->validate([
            'plantilla_name' => 'required|string',
            'fields' => 'required|array',
        ]);
    
        $plantillaName = $request->input('plantilla_name');
        $fields = $request->input('fields');
    
        // Utiliza el Query Builder para verificar si la colección ya existe
        $collections = DB::connection('mongodb')->listCollections();
    
        foreach ($collections as $collection) {
            if ($collection->getName() === $plantillaName) {
                return response()->json(['error' => "La colección '{$plantillaName}' ya existe."], 400);
            }
        }
    
        // Crea la migración dinámicamente
        $plantillaName = str_replace([' ', '_'], '', $plantillaName);

        $migrationCode = $this->generateMigrationCode($plantillaName, $fields);
        $migrationFileName = now()->format('Y_m_d_His') . '_create_' .Str::snake($plantillaName) . '_collection.php';
        file_put_contents(database_path('migrations/' . $migrationFileName), $migrationCode);

        Artisan::call('migrate', ['--path' => 'database/migrations']);
        
        // Genera el modelo dinámicamente
        $modelCode = $this->generateModelCode($plantillaName, $fields);
        $modelFileName = app_path("Models/{$plantillaName}.php");
        file_put_contents($modelFileName, $modelCode);
    
        return response()->json(['message' => 'Migración y modelo creados con éxito'], 201);
    }
    




    public function update(Request $request, $plantillaName)
    {

        log::info($request);
        $request->validate([
            'fields' => 'required|array',
        ]);

        $fields = $request->input('fields');

        // Utiliza el Query Builder para verificar si la colección ya existe
        $collections = DB::connection('mongodb')->listCollections();

        $collectionExists = false;

        foreach ($collections as $collection) {
            if ($collection->getName() === $plantillaName) {
                $collectionExists = true;
                break;
            }
        }

        if (!$collectionExists) {
            return response()->json(['error' => "La colección '{$plantillaName}' no existe."], 404);
        }

        // Crea la migración de actualización dinámicamente
        $updateMigrationCode = $this->generateUpdateMigrationCode($plantillaName, $fields);
        $updateMigrationFileName = now()->format('Y_m_d_His') . '_update_' . Str::plural(Str::snake($plantillaName)) . '_collection.php';
        file_put_contents(database_path('migrations/' . $updateMigrationFileName), $updateMigrationCode);

        // Ejecuta la migración de actualización
        Artisan::call('migrate', ['--path' => "database/migrations/{$updateMigrationFileName}"]);

        $this->updateModel($plantillaName, $fields);

        return response()->json(['message' => 'Migración de actualización creada y ejecutada con éxito'], 200);
    }

    
    private function updateModel($plantillaName, $fields)
{
    // Obtén la ruta del modelo
    $modelPath = app_path("Models/{$plantillaName}.php");

    // Verifica si el archivo del modelo existe
    if (file_exists($modelPath)) {
        // Lee el contenido actual del modelo
        $currentModelCode = file_get_contents($modelPath);

        // Busca la línea que contiene el array $guarded
        preg_match('/protected\s+\$guarded\s*=\s*\[[^\]]*\];/i', $currentModelCode, $matchesGuarded);

        // Busca la línea que contiene el array $fieldTypes
        preg_match('/public\s+static\s+\$fieldTypes\s*=\s*\[[^\]]*\];/i', $currentModelCode, $matchesFieldTypes);

        // Busca la línea que contiene el array $requiredFields
        preg_match('/public\s+static\s+\$requiredFields\s*=\s*\[[^\]]*\];/i', $currentModelCode, $matchesRequiredFields);

        // Busca la línea que contiene el array $fieldAliases
        preg_match('/public\s+static\s+\$fieldAliases\s*=\s*\[[^\]]*\];/i', $currentModelCode, $matchesFieldAliases);

        if ($matchesGuarded && $matchesFieldTypes && $matchesRequiredFields && $matchesFieldAliases) {
            // Obtiene las líneas encontradas y elimina los caracteres no deseados
            $guardedLine = trim($matchesGuarded[0], " \t\n\r\0\x0B");
            $fieldTypesLine = trim($matchesFieldTypes[0], " \t\n\r\0\x0B");
            $requiredFieldsLine = trim($matchesRequiredFields[0], " \t\n\r\0\x0B");
            $fieldAliasesLine = trim($matchesFieldAliases[0], " \t\n\r\0\x0B");

            // Construye el nuevo array de campos protegidos
            $newGuarded = "protected \$guarded = ['_id', " . implode(', ', array_map(function ($field) {
                return "'{$field['name']}'";
            }, $fields)) . "];";

            // Construye el nuevo array de tipos de campos
            $newFieldTypes = "public static \$fieldTypes = [\n            " . implode(', ', array_map(function ($field) {
                return "'{$field['name']}' => '{$field['type']}'";
            }, $fields)) . ",\n        ];";

            // Construye el nuevo array de campos obligatorios
            $requiredFields = array_filter($fields, function ($field) {
                return isset($field['required']) && $field['required'];
            });

            $newRequired = "public static \$requiredFields = ['" . implode("', '", array_map(function ($field) {
                return $field['name'];
            }, $requiredFields)) . "'];";

            // Construye el nuevo array de alias para los campos
            $newAliases = "public static \$fieldAliases = [\n            " . implode(', ', array_map(function ($field) {
                // Usa title_case para generar alias más legibles
                $alias = Str::title(str_replace('_', ' ', $field['name']));
                return "'{$field['name']}' => '{$alias}'";
            }, $fields)) . ",\n        ];";

            // Reemplaza el array de campos protegidos en el código actual del modelo
            $newModelCode = str_replace($guardedLine, $newGuarded, $currentModelCode);

            // Reemplaza el array de tipos de campos en el código actual del modelo
            $newModelCode = str_replace($fieldTypesLine, $newFieldTypes, $newModelCode);

            // Reemplaza el array de campos obligatorios en el código actual del modelo
            $newModelCode = str_replace($requiredFieldsLine, $newRequired, $newModelCode);

            // Reemplaza el array de alias para los campos en el código actual del modelo
            $newModelCode = str_replace($fieldAliasesLine, $newAliases, $newModelCode);

            // Guarda el nuevo código en el archivo del modelo
            file_put_contents($modelPath, $newModelCode);
        }
    }
}




    

    

    private function generateMigrationCode($plantillaName, $fields)
    {
        $migrationCode = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create{$plantillaName}Collection extends Migration
{
    public function up()
    {
        // Crea la colección en MongoDB si no existe
        if (!Schema::connection('mongodb')->hasCollection('{$plantillaName}')) {
            Schema::connection('mongodb')->create('{$plantillaName}', function (Blueprint \$collection) {\n";

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];

            // Ajusta según tus necesidades
            $migrationCode .= "                \$collection->{$type}('{$name}');\n";
        }

        $migrationCode .= "                \$collection->timestamps();\n";

        $migrationCode .= "            });        }
    }

    public function down()
    {
        // Elimina la colección en MongoDB
        Schema::connection('mongodb')->dropIfExists('{$plantillaName}');
    }
}";

        return $migrationCode;
    }



    private function generateModelCode($plantillaName, $fields)
{
    $modelName = Str::studly($plantillaName);

    $modelCode = "<?php

namespace App\Models;

use Jenssegers\\Mongodb\\Eloquent\\Model;

class {$modelName} extends Model
{   
    protected \$collection = '{$plantillaName}'; // Establece el nombre de la colección

    protected \$guarded = ['_id', ";

    foreach ($fields as $field) {
        $modelCode .= "'{$field['name']}', ";
    }

    $modelCode .= "];

    // Define los tipos de datos de los campos de manera estática
    public static \$fieldTypes = [
        ";
    
    foreach ($fields as $field) {
        $modelCode .= "'{$field['name']}' => '{$field['type']}', ";
    }

    $modelCode .= "
    ];

    // Define alias para los campos
    public static \$fieldAliases = [
        ";
    
    foreach ($fields as $field) {
        // Usa title_case para generar alias más legibles
        $alias = Str::title(str_replace('_', ' ', $field['name']));
        $modelCode .= "'{$field['name']}' => '{$alias}', ";
    }

    $modelCode .= "
    ];

    // Define los campos obligatorios
    public static \$requiredFields = [
        ";
    
    foreach ($fields as $field) {
        // Marca los campos como obligatorios si se establece la propiedad 'required'
        if (isset($field['required']) && $field['required']) {
            $modelCode .= "'{$field['name']}', ";
        }
    }

    $modelCode .= "
    ];
}";

    return $modelCode;
}

    



    private function generateUpdateMigrationCode($plantillaName, $fields)
    {
        $migrationCode = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Update{$plantillaName}Collection extends Migration
{
    public function up()
    {
        Schema::connection('mongodb')->table('{$plantillaName}', function (Blueprint \$collection) {\n";

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];

            // Ajusta según tus necesidades
            $migrationCode .= "                \$collection->{$type}('{$name}')->nullable();\n";
        }

        $migrationCode .= "            });
    }

    public function down()
    {
        // Puedes revertir los cambios en la migración de actualización si es necesario
        // En este caso, simplemente eliminamos los campos agregados
        Schema::connection('mongodb')->table('{$plantillaName}', function (Blueprint \$collection) {\n";

        foreach ($fields as $field) {
            $name = $field['name'];

            $migrationCode .= "                \$collection->dropColumn('{$name}');\n";
        }

        $migrationCode .= "            });
    }
}";

        return $migrationCode;
    }

    


    public function storeDocument(Request $request, $plantillaName)
{
    $request->validate([
        'document_data' => 'required|array',
        'files.*' => 'nullable|file|mimes:pdf,jpg,png,mp4,mp3,wav|max:20480', // Ajusta según tus necesidades
        // ... otros campos de validación si es necesario
    ]);

    Log::info('Datos recibidos desde Angular:', $request->all());

    $documentData = $request->input('document_data');

    // Procesar archivos si están presentes
    if ($request->hasFile('files')) {
        $files = $request->file('files');
        $uploadedFiles = [];

        foreach ($files as $key => $file) {
            $fileInfo = [
                'index' => $key + 1,
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
            ];

            Log::info("Información del archivo #{$fileInfo['index']}:", $fileInfo);

            $filePath = $file->store('uploads', 'public'); // Ajusta el nombre y la ruta según tus necesidades
            $uploadedFiles[] = $filePath;

        }

        // Puedes ajustar el límite según tus necesidades (en este caso, 10 archivos)
        if (count($uploadedFiles) > 10) {
            // Si se excede el límite, puedes manejar el error aquí
            return response()->json(['error' => 'No puedes subir más de 10 archivos.'], 400);
        }

        $documentData['Recurso Digital'] = $uploadedFiles;

    }

    // Resto del código...

    // Verifica si 'document_data' es un objeto y lo convierte en un array si es necesario
    if (!is_array($documentData)) {
        $documentData = [$documentData];
    }

    // Inserta el documento en la colección
    $documentData['created_at'] = Carbon::now()->toDateTimeString();
    $documentData['updated_at'] = Carbon::now()->toDateTimeString();

    DB::connection('mongodb')->collection($plantillaName)->insert($documentData);

    return response()->json(['message' => 'Documento creado con éxito'], 201);
}


    
    











    
    
    public function getDocumentbyid($plantillaName, $documentId)
    {
        // Obtiene un documento específico de la colección
        $document = DB::connection('mongodb')->collection($plantillaName)->find($documentId);

        if ($document) {
            return response()->json($document);
        } else {
            return response()->json(['error' => "Documento no encontrado"], 404);
        }
    }




    public function updateDocument(Request $request, $plantillaName, $documentId)
    {
        try {
            // Añade logs para depuración
            Log::info('Solicitud entrante:', ['request' => $request->all()]);
    
            $request->validate([
                'document_data' => 'required|array',
                'files.*' => 'nullable|file|mimes:pdf,jpg,png,mp4,mp3,wav|max:20480',
            ]);
    
            $documentData = $request->input('document_data');
            Log::info('Datos del documento:', ['document_data' => $documentData]);
    
            $modelClass = "App\\Models\\{$plantillaName}";
    
            if (class_exists($modelClass)) {
                $document = $modelClass::find($documentId);
    
                if ($document) {
                    // Verifica si 'Recurso Digital' está presente y es un array
                    if (array_key_exists('Recurso Digital', $documentData) && is_array($documentData['Recurso Digital'])) {
                        // Actualiza los archivos solo si se proporciona 'Recurso Digital' en la solicitud
                        foreach ($documentData['Recurso Digital'] as $index => $file) {
                            // Mover y almacenar el archivo
                            $filePath = $file->store('uploads');
                            $document->Recurso_Digital[] = $filePath;
                        }
                    }
    
                    // Actualiza otros campos dinámicamente
                    foreach ($documentData as $key => $value) {
                        if ($key !== 'Recurso Digital') {
                            $document->$key = $value;
                        }
                    }
    
                    $document->save();
    
                    Log::info('Documento actualizado con éxito');
                    return response()->json(['message' => 'Documento actualizado con éxito']);
                } else {
                    Log::error('Error al actualizar el documento: Documento no encontrado');
                    return response()->json(['error' => 'Error al actualizar el documento: Documento no encontrado'], 404);
                }
            } else {
                Log::error("Error al actualizar el documento: Modelo '{$modelClass}' no encontrado");
                return response()->json(['error' => "Error al actualizar el documento: Modelo '{$modelClass}' no encontrado"], 404);
            }
        } catch (\Exception $e) {
            // Captura la excepción y añade logs
            Log::error('Error al procesar la solicitud: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar la solicitud', 'exception' => $e->getMessage()], 500);
        }
    }
    








    
    public function deleteDocument($plantillaName, $documentId)
    {
        // Elimina el documento de la colección
        $result = DB::connection('mongodb')->collection($plantillaName)->where('_id', $documentId)->delete();

        if ($result) {
            return response()->json(['message' => 'Documento eliminado con éxito']);
        } else {
            return response()->json(['error' => "Documento no encontrado"], 404);
        }
    }



    public function getAllDocuments($plantillaName)
{
    // Verifica si la colección existe
    $collections = DB::connection('mongodb')->listCollections();
    $collectionExists = false;

    foreach ($collections as $collection) {
        if ($collection->getName() === $plantillaName) {
            $collectionExists = true;
            break;
        }
    }

    if ($collectionExists) {
        // Obtiene todos los documentos de la colección
        $documents = DB::connection('mongodb')->collection($plantillaName)->get();

        return response()->json($documents);
    } else {
        return response()->json(['error' => "La colección '{$plantillaName}' no existe."], 404);
    }
}

public function getFields($plantillaName)
{
    // 1. Obtén el nombre de la colección y el modelo relacionado
    $modelName = 'App\\Models\\' . Str::studly($plantillaName);

    // 2. Verifica que la clase exista
    if (class_exists($modelName)) {
        // 3. Utiliza la información estática del modelo para obtener los campos y sus tipos
        $fields = [];

        foreach ($modelName::$fieldTypes as $fieldName => $fieldType) {
            $fieldAlias = $modelName::$fieldAliases[$fieldName] ?? $fieldName;

            $fields[] = [
                'name' => $fieldName,
                'type' => $fieldType,
                'alias' => $fieldAlias,
                'required' => in_array($fieldName, $modelName::$requiredFields),
            ];
        }

        return response()->json($fields);
    } else {
        return response()->json(['message' => 'Clase no encontrada'], 404);
    }
}



public function buscarPorPalabraClave(Request $request)
{
    // Validar la entrada
    $validator = Validator::make($request->all(), [
        'palabra_clave' => 'required|string',
    ]);

    // Verificar si la validación falla
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Obtener la palabra clave validada
    $palabraClave = $validator->validated()['palabra_clave'];
    $nombreBaseDatos = 'laravel-acervo';

    // Conectar a MongoDB
    $client = new Client();

    // Obtener todas las colecciones en la base de datos
    $colecciones = $client->$nombreBaseDatos->listCollections();

    $resultados = [];

    $coleccionesOmitir = ['carrousel_images', 'migrations', 'personal_access_tokens', 'plantillas_predeterminadas', 'users', 'comentarios'];

    $camposOmitir = ['_id', 'created_at', 'updated_at', 'Recurso Digital'];


    // Iterar sobre cada colección y realizar la búsqueda
    foreach ($colecciones as $coleccion) {
        $nombreColeccion = $coleccion->getName();

         // Verificar si la colección debe ser omitida
         if (in_array($nombreColeccion, $coleccionesOmitir)) {
            continue; // Saltar a la siguiente iteración
        }   

        // Realizar la búsqueda en la colección actual en todos los campos
        $resultadosColeccion = $client->$nombreBaseDatos->$nombreColeccion
            ->find([
                '$or' => $this->construirExpresionesDeBusqueda($palabraClave, $nombreColeccion, $camposOmitir)
            ])
            ->toArray();

        // Agregar el nombre de la colección como un campo adicional a cada documento
        foreach ($resultadosColeccion as &$documento) {
            $documento['tipo_coleccion'] = $nombreColeccion;
        }

        // Verificar si hay resultados antes de agregar al array
        if ($resultadosColeccion) {
            $resultados = array_merge($resultados, $resultadosColeccion);
        }
    }

    return response()->json($resultados);
}

// Construir expresiones de búsqueda para todos los campos en la colección
private function construirExpresionesDeBusqueda($palabraClave, $nombreColeccion, $camposOmitir)
{
    $expresiones = [];

    // Obtener todos los documentos en la colección
    $client = new Client();
    $colecciones = $client->selectDatabase('laravel-acervo')->selectCollection($nombreColeccion);
    $documentos = $colecciones->find();

    foreach ($documentos as $documento) {
        foreach ($documento as $campo => $valor) {
            // Verificar si el campo debe ser omitido
            if (in_array($campo, $camposOmitir)) {
                continue; // Saltar a la siguiente iteración
            }

            $expresiones[] = [$campo => ['$regex' => $palabraClave]];
        }
    }

    // Verificar si hay expresiones antes de devolver el resultado
    if (empty($expresiones)) {
        // Devolver una expresión predeterminada (puedes ajustar según tus necesidades)
        $expresiones = [['_id' => ['$exists' => true]]];
    }

    return $expresiones;
}



public function avanzadabusqueda(Request $request)
{
    $palabrasClave = $request->input('palabras_clave');
    $nombreColeccion = $request->input('nombre_coleccion');
    $nombreBaseDatos = 'laravel-acervo';

    // Conectar a MongoDB
    $client = new Client();

    // Obtener todas las colecciones en la base de datos
    $colecciones = $client->$nombreBaseDatos->listCollections();

    $resultados = [];

    // Iterar sobre cada colección y realizar la búsqueda
    foreach ($colecciones as $coleccion) {
        $nombreColeccionActual = $coleccion->getName();

        // Realizar la búsqueda en la colección actual
        if ($nombreColeccionActual === $nombreColeccion) {
            // Obtener los campos del modelo asociado a la colección
            $campos = $this->getFields($nombreColeccionActual);

            // Si no hay palabras clave, obtener todos los documentos de la colección
            if (empty($palabrasClave)) {
                $resultadosColeccion = $client->$nombreBaseDatos->$nombreColeccionActual
                    ->find()
                    ->toArray();
            } else {
                // Construir un arreglo de condiciones AND para cada palabra clave en cada campo
                $condicionesAnd = [];
                foreach ($palabrasClave as $palabra) {
                    $condicionesPalabra = [];
                    foreach ($campos as $campo) {
                        if (is_array($campo)) {
                            foreach ($campo as $campoSub) {
                                if (!empty($campoSub)) {
                                    $condicionesPalabra[] = [$campoSub['name'] => ['$regex' => $palabra]];
                                }
                            }
                        } else {
                            if (!empty($campo)) {
                                $condicionesPalabra[] = [strval($campo) => ['$regex' => $palabra]];
                            }
                        }
                    }
                    // Agregar las condiciones de la palabra clave actual al conjunto AND
                    if (!empty($condicionesPalabra)) {
                        $condicionesAnd[] = ['$or' => $condicionesPalabra];
                    }
                }

                // Realizar la búsqueda en la colección actual solo si hay condiciones AND
                if (!empty($condicionesAnd)) {
                    $resultadosColeccion = $client->$nombreBaseDatos->$nombreColeccionActual
                        ->find(['$and' => $condicionesAnd])
                        ->toArray();
                } else {
                    // Si no hay condiciones AND, mostrar todos los documentos de la colección
                    $resultadosColeccion = $client->$nombreBaseDatos->$nombreColeccionActual
                        ->find()
                        ->toArray();
                }
            }

            // Agregar el nombre de la colección como un campo adicional a cada documento
            foreach ($resultadosColeccion as &$documento) {
                $documento['tipo_coleccion'] = $nombreColeccionActual;
            }

            // Agregar los resultados de la colección actual al resultado final
            $resultados = array_merge($resultados, $resultadosColeccion);
        }
    }

    // Retorna los resultados como una respuesta JSON
    return response()->json($resultados);
}




public function obtenerPlantillasPredeterminadas()
{
    $plantillas = PlantillaPredeterminada::all();

    return response()->json($plantillas);
}



public function descargarConMarcaAgua($plantillaName, $documentId)
{
    // Obtiene el documento de la colección
    $document = DB::connection('mongodb')->collection($plantillaName)->find($documentId);

    if (!$document) {
        return response()->json(['error' => "Documento no encontrado"], 404);
    }

    // Ruta donde se almacenan los archivos en tu servidor
    $archivosPath = storage_path('app/public/');
    $zipFileName = 'recursos_digitales_marca_de_agua.zip';

    // Crea un archivo ZIP
    $zip = new ZipArchive;
    if ($zip->open($archivosPath . $zipFileName, ZipArchive::CREATE) === true) {

        // Ruta de tu marca de agua (ajústala según tu estructura de directorios)
        $marcaDeAguaPath = public_path('images/tu-marca-de-agua.png');

        // Verifica si la marca de agua existe
        if (!file_exists($marcaDeAguaPath)) {
            return response()->json(['error' => 'Marca de agua no encontrada'], 404);
        }

        // Itera sobre cada archivo en el documento y aplica la marca de agua
        foreach ($document['Recurso Digital'] as $index => $archivo) {
            $archivoPath = $archivosPath . $archivo;

            // Verifica si el archivo existe
            if (file_exists($archivoPath)) {
                // Carga la imagen principal usando Intervention Image
                $imagenConMarcaAgua = Image::make($archivoPath);

                // Carga la marca de agua
                $marcaDeAgua = Image::make($marcaDeAguaPath);

                // Ajusta el tamaño de la marca de agua al tamaño de la imagen principal
                $marcaDeAgua->fit($imagenConMarcaAgua->width(), $imagenConMarcaAgua->height());

                // Calcula la posición central para la marca de agua
                $posicionX = intval(($imagenConMarcaAgua->width() - $marcaDeAgua->width()) / 2);
                $posicionY = intval(($imagenConMarcaAgua->height() - $marcaDeAgua->height()) / 2);

                // Aplica la marca de agua a la imagen
                $imagenConMarcaAgua->insert($marcaDeAgua, 'top-left', $posicionX, $posicionY);

                // Guarda la imagen con marca de agua en el archivo ZIP
                $zip->addFromString("recurso_digital_marca_de_agua_$index.png", $imagenConMarcaAgua->encode());
            } else {
                // Log o manejo de error indicando que el archivo no existe
                Log::error("El archivo $archivo no existe en la ruta $archivoPath");
            }
        }

        // Cierra el archivo ZIP
        $zip->close();

        // Devuelve el archivo ZIP al cliente
        return response()->download($archivosPath . $zipFileName)->deleteFileAfterSend(true);
    }

    return response()->json(['error' => 'Error al crear el archivo ZIP'], 500);
}






public function obtenerUltimosDocumentos()
{
    // Obtén todas las colecciones
    $colecciones = DB::connection('mongodb')->listCollections();

    $ultimosDocumentos = [];

    foreach ($colecciones as $coleccion) {
        // Obtén el nombre de la colección actual
        $nombreColeccion = $coleccion->getName();

        // Omitir colecciones específicas (ajusta según tus necesidades)
        if ($nombreColeccion === 'carrousel_images' || $nombreColeccion === 'comentarios' || $nombreColeccion === 'migrations' || $nombreColeccion === 'users' || $nombreColeccion === 'personal_access_tokens' || $nombreColeccion === 'plantillas_predeterminadas') {
            continue;
        }

        // Verifica si la colección tiene al menos 1 documento
        if (DB::connection('mongodb')->collection($nombreColeccion)->count() > 0) {
            // Obtiene los últimos 10 documentos ordenados por fecha de creación descendente
            $documentos = DB::connection('mongodb')->collection($nombreColeccion)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            // Agrega los documentos a la lista final
            $ultimosDocumentos[$nombreColeccion] = $documentos;
        }
    }

    return response()->json($ultimosDocumentos);
}

}
