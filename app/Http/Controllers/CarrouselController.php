<?php

namespace App\Http\Controllers;
use App\Models\CarrouselImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CarrouselController extends Controller
{
    public function store(Request $request)
    {
        // Valida la solicitud
        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);
    
        // Procesa la imagen y obtén la ruta temporal
        $imagen = $request->file('imagen');
        $imagenNombre = time() . '.' . $imagen->getClientOriginalExtension();
        $imagen->storeAs('carrousel_images', $imagenNombre, 'public');
    
        // Construye la ruta completa para almacenarla en la colección
        $rutaImagen = asset('storage/carrousel_images/' . $imagenNombre);
    
        // Guarda la información en la colección
        $carrouselImage = new CarrouselImage([
            'imagen' => $rutaImagen,
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
        ]);
    
        $carrouselImage->save();
    }
    
    public function getImagesForCarousel()
    {
        $today = now()->format('Y-m-d H:i:s');
        $images = CarrouselImage::where('fecha_inicio', '<=', $today)
            ->where('fecha_fin', '>=', $today)
            ->get();
    
        return response()->json($images);
    }
    
    
    public function getAllCarrouselImages()
    {
        $carrouselImages = CarrouselImage::all();
    
        return response()->json($carrouselImages);
    }
    

    public function eliminarImagen($id)
{
    $imagen = CarrouselImage::find($id);

    if (!$imagen) {
        return response()->json(['message' => 'Imagen no encontrada'], 404);
    }

    // Elimina la imagen de la colección y la base de datos
    $imagen->delete();

    return response()->json(['message' => 'Imagen eliminada correctamente']);
}

}

