<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id_cliente';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'id_usuario',
        'nombre',
        'apellido',
        'telefono',
        'estado'
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'nombre'    => 'required|min_length[2]',
        'apellido'  => 'permit_empty|min_length[2]',
        'telefono'  => 'permit_empty|numeric',
        'estado'    => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'estado' => [
            'in_list' => 'El estado debe ser 0 o 1.'
        ]
    ];
}
