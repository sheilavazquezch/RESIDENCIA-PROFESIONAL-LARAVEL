<?php

namespace App\Models;

// app/Models/CarrouselImage.php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class CarrouselImage extends Eloquent

{
    protected $collection = 'carrousel_images'; // Establece el nombre de la colección

    protected $fillable = ['imagen', 'fecha_inicio', 'fecha_fin'];
}
