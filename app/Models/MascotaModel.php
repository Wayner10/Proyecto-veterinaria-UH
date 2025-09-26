<?php

namespace App\Models;

use CodeIgniter\Model;

class MascotaModel extends Model
{
    protected $table            = 'mascotas';
    protected $primaryKey       = 'id_mascota';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nombre',
        'especie',
        'raza',
        'edad',
        'peso',
        'id_cliente',
        'estado'
    ];

    protected $useTimestamps = false;

    // Reglas de validación
protected $validationRules = [
    'nombre'      => 'required|min_length[2]|max_length[100]',
    'especie'     => 'required|in_list[Perro,Gato,Ave,Conejo,Otro]',
    'raza'        => 'required|min_length[2]|max_length[100]',
    'edad'        => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
    'peso'        => 'required|decimal|greater_than[0]',
    'id_cliente'  => 'required|is_natural_no_zero',
    'estado'      => 'required|in_list[0,1]'
];

// Mensajes de validación personalizados
protected $validationMessages = [
    'nombre' => [
        'required'    => 'El nombre de la mascota es obligatorio.',
        'min_length'  => 'El nombre debe tener al menos 2 caracteres.',
        'max_length'  => 'El nombre no debe superar los 100 caracteres.'
    ],
    'especie' => [
        'required'    => 'La especie es obligatoria.',
        'in_list'     => 'La especie debe ser Perro, Gato, Ave, Conejo u Otro.'
    ],
    'raza' => [
        'required'    => 'La raza es obligatoria.',
        'min_length'  => 'La raza debe tener al menos 2 caracteres.',
        'max_length'  => 'La raza no debe superar los 100 caracteres.'
    ],
    'edad' => [
        'required'                 => 'La edad es obligatoria.',
        'numeric'                  => 'La edad debe ser un número.',
        'greater_than_equal_to'   => 'La edad no puede ser negativa.',
        'less_than_equal_to'      => 'La edad debe ser razonable (menos de 100 años).'
    ],
    'peso' => [
        'required'     => 'El peso es obligatorio.',
        'decimal'      => 'El peso debe tener un formato decimal (ej. 2.5).',
        'greater_than' => 'El peso debe ser mayor a cero.'
    ],
    'id_cliente' => [
        'required'            => 'Debes seleccionar un cliente asociado.',
        'is_natural_no_zero' => 'Cliente inválido. Por favor selecciona uno válido.'
    ],
    'estado' => [
        'required' => 'El campo estado es obligatorio.',
        'in_list'  => 'Estado inválido. Debe ser 1 (activo) o 0 (inactivo).'
    ]
];

}
