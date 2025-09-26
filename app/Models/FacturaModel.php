<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaModel extends Model
{
    protected $table            = 'facturas';
    protected $primaryKey       = 'id_factura';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'id_cliente',
        'fecha',
        'total',
        'estado'
    ];

    protected $useTimestamps = false;

    // Reglas de validación
    protected $validationRules = [
        'id_cliente' => 'required|is_natural_no_zero',
        'fecha'      => 'required|valid_date[Y-m-d]',
        'total'      => 'permit_empty|decimal|greater_than_equal_to[0]',
        'estado'     => 'required|in_list[0,1]'
    ];

    // Mensajes personalizados
    protected $validationMessages = [
        'id_cliente' => [
            'required' => 'Debes seleccionar un cliente.',
            'is_natural_no_zero' => 'Cliente inválido.'
        ],
        'fecha' => [
            'required' => 'La fecha de la factura es obligatoria.',
            'valid_date' => 'La fecha no tiene un formato válido (AAAA-MM-DD).'
        ],
        'total' => [
            'required' => 'El total de la factura es obligatorio.',
            'decimal' => 'El total debe ser un número decimal (ej. 125.50).',
            'greater_than_equal_to' => 'El total no puede ser negativo.'
        ],
        'estado' => [
            'required' => 'El estado de la factura es obligatorio.',
            'in_list' => 'El estado debe ser 0 (anulada) o 1 (activa).'
        ]
    ];
}
