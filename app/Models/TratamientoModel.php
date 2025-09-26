<?php

namespace App\Models;

use CodeIgniter\Model;

class TratamientoModel extends Model
{
    protected $table      = 'tratamientos';
    protected $primaryKey = 'id_tratamiento';

    protected $allowedFields = [
        'id_mascota',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    protected $returnType    = 'array';
    protected $useTimestamps = false;
}
