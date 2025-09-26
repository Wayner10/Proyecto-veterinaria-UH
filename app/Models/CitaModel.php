<?php

namespace App\Models;

use CodeIgniter\Model;

class CitaModel extends Model
{
    protected $table = 'citas';
    protected $primaryKey = 'id_cita';

    protected $allowedFields = [
        'id_mascota',
        'id_veterinario',
        'fecha_hora',
        'motivo',
        'estado'
    ];

    // Validaciones automáticas al usar insert() o update()
    protected $validationRules = [
        'id_mascota'     => 'required|integer',
        'id_veterinario' => 'required|integer',
        'fecha_hora'     => 'required|valid_date[Y-m-d H:i:s]',
        'motivo'         => 'required|string|min_length[5]',
        'estado'         => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'id_mascota' => [
            'required' => 'Debe seleccionar una mascota.',
            'integer'  => 'ID de mascota inválido.'
        ],
        'id_veterinario' => [
            'required' => 'Debe estar asignado a un veterinario.',
            'integer'  => 'ID de veterinario inválido.'
        ],
        'fecha_hora' => [
            'required'   => 'La fecha y hora son obligatorias.',
            'valid_date' => 'Formato de fecha incorrecto (debe ser Y-m-d H:i:s).'
        ],
        'motivo' => [
            'required'    => 'Debe especificar el motivo de la cita.',
            'min_length'  => 'El motivo debe tener al menos 5 caracteres.'
        ],
        'estado' => [
            'required' => 'Debe indicar el estado de la cita.',
            'in_list' => 'El estado debe ser 0 (inactiva) o 1 (activa).'
        ]
    ];
}
