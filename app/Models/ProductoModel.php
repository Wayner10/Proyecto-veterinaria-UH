<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table            = 'productos';
    protected $primaryKey       = 'id_producto';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nombre',
        'descripcion',
        'precio',
        'estado'
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'nombre'      => 'required|min_length[2]',
        'precio'      => 'required|decimal',
        'estado'      => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre del producto es obligatorio.',
        ],
        'precio' => [
            'required' => 'Debe especificar un precio.',
            'decimal'  => 'El precio debe ser un número decimal válido.'
        ],
        'estado' => [
            'in_list' => 'El estado debe ser 0 o 1.'
        ]
    ];
}
