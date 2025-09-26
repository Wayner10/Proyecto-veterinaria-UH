<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id_usuario';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Cambia a true si tienes columna 'deleted_at'

    protected $allowedFields    = [
        'nombre',
        'correo_electronico',
        'contrasena',
        'id_rol',
        'estado'
    ];

    // Fechas automáticas
    protected $useTimestamps    = false;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // Validación automática al usar save() o insert()
    protected $validationRules = [
        'nombre'             => 'required|min_length[3]|max_length[100]',
        'correo_electronico' => 'required|valid_email|max_length[100]',
        'contrasena'         => 'permit_empty|min_length[6]|max_length[255]',
        'id_rol'             => 'required|in_list[1,2,3,4]',
        'estado'             => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre es obligatorio.',
            'min_length' => 'Debe tener al menos 3 caracteres.'
        ],
        'correo_electronico' => [
            'required'    => 'El correo electrónico es obligatorio.',
            'valid_email' => 'Debe ser un correo válido.',
        ],
        'contrasena' => [
            'min_length' => 'La contraseña debe tener al menos 6 caracteres.',
        ],
        'id_rol' => [
            'required' => 'Debe seleccionar un rol.',
            'in_list' => 'Rol no válido.'
        ],
        'estado' => [
            'required' => 'Debe indicar el estado.',
            'in_list' => 'Estado no válido.'
        ]
    ];

    protected $skipValidation = false;
}
