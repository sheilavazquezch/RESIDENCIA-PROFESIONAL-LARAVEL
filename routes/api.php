<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarrouselController;
use App\Http\Controllers\ComentarioController;
use App\Models\Comentario;
use App\Models\CarrouselImage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


//Sin autenticacion para poder mostrarlo en el home-user
Route::get('/comentarios/aprobados/{documentoId}', [ComentarioController::class, 'comentariosAprobados']);
Route::get('admin/carrousel-images', [CarrouselController::class, 'getImagesForCarousel']);
Route::get('/descargar-con-marca-agua/{plantillaName}/{documentId}', [PlantillaController::class, 'descargarConMarcaAgua']);
//Buscar por palabra clave
Route::post('plantillas/buscar-palabra-clave', [PlantillaController::class, 'buscarPorPalabraClave']);
//Buscar por campos especificos 
Route::post('plantillas/avanzada-busqueda', [PlantillaController::class, 'avanzadabusqueda'] );
//Ultimos 10
Route::get('/plantillas/last',[PlantillaController::class, 'obtenerUltimosDocumentos']);
Route::get('/plantillas/{plantillaName}/documentos/{documentId}', [PlantillaController::class, 'getDocumentbyid']);
Route::get('/plantillas/get', [PlantillaController::class, 'get']);
Route::get('plantillas/{plantillaName}/fields', [PlantillaController::class, 'getFields']);


//REGISTRO DE USUARIOS

//Registro de usuarios
Route::post('/user/register', [AuthController::class, 'userRegister'])->name('user.register');
// Rutas para el inicio de sesión de usuarios finales
Route::post('/user/login', [AuthController::class, 'userLogin'])->name('user.login');
// Rutas para el inicio de sesión de administradores
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login');
// Ruta para cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
//Ruta para perfil de usuario


Route::middleware(['auth:api'])->group(function () {
    // Rutas protegidas con el middleware de autenticación

    Route::get('/user/profile', [AuthController::class, 'profile']);

    // Rutas para actualizar el perfil del usuario
    Route::put('/users/edit/{id}', [AuthController::class, 'updateUserProfile']);
});


Route::get('/user/{id}', [AuthController::class ,'getUserById']);


Route::get('/plantillas/comentario-id/{documentId}',[PlantillaController::class, 'buscarDocumentoPorId']);
Route::post('/enviar-correo', [ComentarioController::class, 'enviarCorreo']);



//REGISTRO DE USUARIOS ADMIN
Route::middleware(['auth:api', 'checkrole:Admin'])->group(function () {

    Route::get('/get-users-administrativos', [AuthController::class, 'getUsersAdministrativos']);
    Route::delete('usuarios/{id}', [AuthController::class, 'eliminarUsuarioAdministrativo']);
    Route::post('/admin/register', [AuthController::class, 'adminRegister']);
    Route::put('usuarios/{id}', [AuthController::class, 'updateAdministrativos']);

});
Route::get('user/{id}', [AuthController::class, 'getUserById']);


//PLANTILLAS

Route::middleware(['auth:api', 'checkrole:Admin,Plantillas'])->group(function () {

// Crear una plantilla
Route::post('/plantillas/create', [PlantillaController::class, 'create']);
// Actualziar una plantilla
Route::put('/plantillas/update/{plantillaName}', [PlantillaController::class, 'update']);
//Elimianr una P´lantilla
Route::delete('plantillas/delete/{plantillaName}', [PlantillaController::class, 'delete']);
//Obtener los campos de las plantillas
//Obtener las plantillas predeterminadas
Route::get('/obtener-plantillas-predeterminadas', [PlantillaController::class, 'obtenerPlantillasPredeterminadas']);

});


Route::post('/plantillas/{plantillaName}/documentos/{documentId}', [PlantillaController::class, 'updateDocument']);

//DOCUMENTOS

Route::middleware(['auth:api', 'checkrole:Admin,Capturista'])->group(function () {

Route::get('/plantillas/getforDocuments', [PlantillaController::class, 'getforDocuments']);

//Crear un documento
Route::post('/plantillas/{plantillaName}/documentos', [PlantillaController::class, 'storeDocument']);
//Obtener un documento por su id
//Actualizar un documento
//Eliminar un documento
Route::delete('/plantillas/{plantillaName}/documentos/{documentId}', [PlantillaController::class, 'deleteDocument']);
//Obtener todos los documentos 
Route::get('/plantillas/{plantillaName}/documentos', [PlantillaController::class, 'getAllDocuments']);

});




Route::post('/comentarios', [ComentarioController::class, 'store']);

//COMENTARIOS
Route::middleware(['auth:api', 'checkrole:Admin,Validador'])->group(function () {

//Crear un comentario 
Route::put('/comentarios/aprobar/{id}', [ComentarioController::class,'aprobarComentario']);
Route::delete('/comentarios/denegar/{id}', [ComentarioController::class,'denegarComentario']);
Route::get('/comentarios/pendientes', [ComentarioController::class,'obtenerComentariosPendientes']);
Route::get('/comentarios/aprobados', [ComentarioController::class,'obtenerComentariosAprobados']);

});


//CARRUSEL 
Route::middleware(['auth:api', 'checkrole:Admin,Carrusel'])->group(function () {

    Route::delete('admin/eliminar-carrousel/{id}', [CarrouselController::class, 'eliminarImagen']);
    Route::post('admin/carrousel',[CarrouselController::class, 'store']);
    Route::get('admin/carrousel-images/all', [CarrouselController::class, 'getAllCarrouselImages']);
    
});


 

    
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
