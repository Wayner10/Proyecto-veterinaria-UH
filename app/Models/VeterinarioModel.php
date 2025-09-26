<?php

namespace App\Models;

use CodeIgniter\Model;

class VeterinarioModel extends Model
{
    protected $table            = 'veterinarios';
    protected $primaryKey       = 'id_veterinario';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; 

    protected $allowedFields    = [
        'nombre',
        'apellido',
        'telefono',
        'especialidad',
        'estado'
    ];

    protected $useTimestamps    = false;

    protected $validationRules = [
        'nombre'       => 'required|min_length[3]|max_length[100]',
        'apellido'     => 'permit_empty|min_length[3]|max_length[100]',
        'telefono'     => 'permit_empty|min_length[6]|max_length[25]',
        'especialidad' => 'permit_empty|min_length[3]|max_length[100]',
        'estado'       => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required'   => 'El nombre es obligatorio.',
            'min_length' => 'Debe tener al menos 3 caracteres.'
        ],
        'apellido' => [
            'min_length' => 'Debe tener al menos 3 caracteres.'
        ],
        'telefono' => [
            'min_length' => 'El teléfono debe tener al menos 6 caracteres.',
        ],
        'especialidad' => [
            'min_length' => 'Debe tener al menos 3 caracteres.'
        ],
        'estado' => [
            'required' => 'Debe indicar el estado.',
            'in_list'  => 'Estado no válido.'
        ]
    ];

    protected $skipValidation = false;
}
