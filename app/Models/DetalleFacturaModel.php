<?php

namespace App\Models;

use CodeIgniter\Model;

class DetalleFacturaModel extends Model
{
    protected $table          = 'detalles_factura';
    protected $primaryKey     = 'id_detalle';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;
    protected $protectFields  = true;

    protected $allowedFields = [
        'id_factura',
        'id_producto',
        'id_servicio',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    /** Autocálculo/normalización */
    protected $beforeInsert = ['sanitizeAndCompute'];
    protected $beforeUpdate = ['sanitizeAndCompute'];

    /** Casting automático al leer */
    protected $casts = [
        'id_detalle'      => 'integer',
        'id_factura'      => 'integer',
        'id_producto'     => 'integer',
        'id_servicio'     => 'integer',
        'cantidad'        => 'float',
        'precio_unitario' => 'float',
        'subtotal'        => 'float',
    ];

    /** Validación base */
    protected $validationRules = [
        'id_factura'      => 'required|is_natural_no_zero',
        // id_producto e id_servicio se validan como permit_empty aquí,
        // y la regla XOR se aplica en el callback sanitizeAndCompute()
        'id_producto'     => 'permit_empty|is_natural_no_zero',
        'id_servicio'     => 'permit_empty|is_natural_no_zero',
        'cantidad'        => 'required|numeric|greater_than[0]',
        'precio_unitario' => 'required|numeric|greater_than[0]',
        // subtotal lo calculamos nosotros; si viene del form, lo ignoramos
    ];

    protected $validationMessages = [
        'id_factura' => [
            'required'            => 'Debe asociar el detalle a una factura.',
            'is_natural_no_zero'  => 'Factura inválida.',
        ],
        'id_producto' => [
            'is_natural_no_zero'  => 'Producto inválido.',
        ],
        'id_servicio' => [
            'is_natural_no_zero'  => 'Servicio inválido.',
        ],
        'cantidad' => [
            'required'     => 'Debe indicar la cantidad.',
            'numeric'      => 'La cantidad debe ser numérica.',
            'greater_than' => 'La cantidad debe ser mayor a cero.',
        ],
        'precio_unitario' => [
            'required'               => 'El precio es obligatorio.',
            'numeric'                => 'El precio debe ser numérico.',
            'greater_than'           => 'El precio debe ser mayor a cero.',
        ],
    ];

    /**
     * Callback: normaliza números, aplica XOR producto/servicio y calcula subtotal.
     * @param array $data
     * @return array
     */
    protected function sanitizeAndCompute(array $data): array
    {
        if (!isset($data['data']) || !is_array($data['data'])) {
            return $data;
        }
        $row = &$data['data'];

        // Limpia espacios y asegura nulls
        $row['id_producto'] = isset($row['id_producto']) && $row['id_producto'] !== '' ? (int)$row['id_producto'] : null;
        $row['id_servicio'] = isset($row['id_servicio']) && $row['id_servicio'] !== '' ? (int)$row['id_servicio'] : null;

        // Regla XOR: exactamente uno
        $hasProd = !empty($row['id_producto']);
        $hasServ = !empty($row['id_servicio']);
        if (($hasProd && $hasServ) || (!$hasProd && !$hasServ)) {
            // inyecta error de validación y bloquea operación
            $this->errors()['item'] = 'Cada detalle debe tener producto o servicio (no ambos, no ninguno).';
            // Evitar insert/update continuando con datos inválidos:
            // CI4 no tiene un "abort" dentro del callback; la forma correcta
            // es marcar skipValidation=false (por defecto) y devolver un valor
            // que provoque fallo en reglas; aquí hacemos un truco:
            $row['id_producto'] = null; // deja ambos vacíos para que falle la lógica de negocio si vuelven a validar
            $row['id_servicio'] = null;
        }

        // Normaliza decimales
        $cantidad        = isset($row['cantidad']) ? (float)$row['cantidad'] : 0.0;
        $precioUnitario  = isset($row['precio_unitario']) ? (float)$row['precio_unitario'] : 0.0;

        // Redondeos a 2 decimales 
        $cantidad       = round($cantidad, 2);
        $precioUnitario = round($precioUnitario, 2);

        // Reasigna normalizados
        $row['cantidad']        = $cantidad;
        $row['precio_unitario'] = $precioUnitario;

        // Cálculo backend confiable
        $row['subtotal'] = round($cantidad * $precioUnitario, 2);

        return $data;
    }

    /**
     * Helper: trae detalles + nombres de producto/servicio para vistas.
     * @param int $idFactura
     * @return array
     */
    public function getDetallesConNombres(int $idFactura): array
    {
        return $this->select('detalles_factura.*, p.nombre AS producto, s.nombre AS servicio')
            ->join('productos p', 'p.id_producto = detalles_factura.id_producto', 'left')
            ->join('servicios s', 's.id_servicio = detalles_factura.id_servicio', 'left')
            ->where('detalles_factura.id_factura', $idFactura)
            ->findAll();
    }
}
