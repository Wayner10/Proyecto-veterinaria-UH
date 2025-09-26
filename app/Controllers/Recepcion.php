<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CitaModel;
use App\Models\ClienteModel;
use App\Models\MascotaModel;
use App\Models\FacturaModel;
use App\Models\DetalleFacturaModel;
use App\Models\UsuarioModel;
use App\Models\ProductoModel;
use App\Models\ServicioModel;
use CodeIgniter\I18n\Time;
use Config\Services;
use Dompdf\Dompdf;
use Dompdf\Options;


class Recepcion extends BaseController
{
    /** Zona horaria por defecto del proyecto */
    private string $tz = 'America/Costa_Rica';

    /** Asegura sesión activa con rol 3 (recepcionista) */
    private function requireRecepcionista(): void
    {
        if (!session()->get('logged_in') || (int) session()->get('id_rol') !== 3) {
            // Nota: podrías guardar intended URL para redireccionar post-login
            redirect()->to('/login')->send();
            exit;
        }
    }

    public function inicio()
    {
        $this->requireRecepcionista();

        $citaModel    = new CitaModel();
        $clienteModel = new ClienteModel();
        $mascotaModel = new MascotaModel();
        $facturaModel = new FacturaModel();

        // Fecha local (CR) en formato Y-m-d
        $hoy = Time::today($this->tz)->toDateString();          // e.g. 2025-08-12
        $manana = Time::tomorrow($this->tz)->toDateString();    // para rango abierto

        // Evita usar DATE(col) que rompe índices; usa rango [hoy, mañana)
        $citasHoy = $citaModel
            ->where('fecha_hora >=', $hoy . ' 00:00:00')
            ->where('fecha_hora <',  $manana . ' 00:00:00')
            ->countAllResults();

        $facturasHoy = $facturaModel
            ->where('fecha >=', $hoy)
            ->where('fecha <',  $manana)
            ->countAllResults();

        $clientes = $clienteModel->countAllResults();
        $mascotas = $mascotaModel->countAllResults();

        return view('recepcion/inicio', compact('citasHoy', 'clientes', 'mascotas', 'facturasHoy'));
    }

    public function citas()
    {
        $this->requireRecepcionista();

        $db = \Config\Database::connect();

        $citas = $db->table('citas c')
            ->select([
                'c.id_cita', 'c.fecha_hora', 'c.estado', 'c.motivo',
                'm.nombre AS mascota',
                'v.nombre AS veterinario',
                'cl.nombre AS cliente_nombre', 'cl.apellido AS cliente_apellido'
            ])
            ->join('mascotas m', 'm.id_mascota = c.id_mascota')
            ->join('clientes cl', 'cl.id_cliente = m.id_cliente')
            ->join('veterinarios v', 'v.id_veterinario = c.id_veterinario')
            ->orderBy('c.fecha_hora', 'DESC')
            ->get()->getResult();

        return view('recepcion/citas', ['citas' => $citas]);
    }

    public function agendar_cita()
    {
        $this->requireRecepcionista();

        $db = \Config\Database::connect();

        $mascotas = $db->table('mascotas')->where('estado', 1)->get()->getResult();
        $veterinarios = $db->table('veterinarios')->where('estado', 1)->get()->getResult();

        return view('recepcion/crear_cita', compact('mascotas', 'veterinarios'));
    }

    public function guardar_cita()
    {
        $this->requireRecepcionista();

        $validation = Services::validation();
        $validation->setRules([
            'id_mascota'     => 'required|is_natural_no_zero',
            'id_veterinario' => 'required|is_natural_no_zero',
            // valid_date acepta "Y-m-d\TH:i"
            'fecha_hora'     => 'required|valid_date[Y-m-d\TH:i]',
            'motivo'         => 'required|min_length[3]|max_length[255]',
        ], [
            'id_mascota.required'      => 'Debe seleccionar una mascota.',
            'id_veterinario.required'  => 'Debe seleccionar un veterinario.',
            'fecha_hora.valid_date'    => 'Formato inválido para la fecha y hora.',
            'motivo.required'          => 'Debe ingresar el motivo de la cita.'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Normaliza a MySQL DATETIME
        $fechaHoraMySQL = str_replace('T', ' ', $this->request->getPost('fecha_hora')) . ':00';

        $data = [
            'id_mascota'     => (int) $this->request->getPost('id_mascota'),
            'id_veterinario' => (int) $this->request->getPost('id_veterinario'),
            'fecha_hora'     => $fechaHoraMySQL,
            'motivo'         => trim($this->request->getPost('motivo')),
            'estado'         => 1,
        ];

        $citaModel = new CitaModel();
        if (!$citaModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Error al guardar la cita.');
        }

        return redirect()->to('/recepcion/citas')->with('success', 'Cita registrada exitosamente.');
    }

    public function editar($id)
    {
        $this->requireRecepcionista();

        $db = \Config\Database::connect();

        $cita = $db->table('citas')
            ->select('
                citas.*,
                mascotas.id_mascota, mascotas.nombre AS mascota, mascotas.id_cliente,
                clientes.nombre AS cliente_nombre, clientes.apellido AS cliente_apellido,
                veterinarios.id_veterinario, veterinarios.nombre AS veterinario, veterinarios.apellido AS vet_apellido
            ')
            ->join('mascotas', 'mascotas.id_mascota = citas.id_mascota')
            ->join('clientes', 'clientes.id_cliente = mascotas.id_cliente')
            ->join('veterinarios', 'veterinarios.id_veterinario = citas.id_veterinario')
            ->where('citas.id_cita', (int)$id)
            ->get()->getRow();

        if (!$cita) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Cita no encontrada.');
        }

        $mascotas = $db->table('mascotas')->where('estado', 1)->get()->getResult();
        $veterinarios = $db->table('veterinarios')->where('estado', 1)->get()->getResult();

        return view('recepcion/editar', compact('cita', 'mascotas', 'veterinarios'));
    }

    public function actualizar($id)
    {
        $this->requireRecepcionista();

        $validation = Services::validation();
        $validation->setRules([
            'id_mascota'     => 'required|is_natural_no_zero',
            'id_veterinario' => 'required|is_natural_no_zero',
            'fecha_hora'     => 'required|valid_date[Y-m-d\TH:i]',
            'motivo'         => 'required|string|min_length[5]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $fechaHoraMySQL = str_replace('T', ' ', $this->request->getPost('fecha_hora')) . ':00';

        $data = [
            'id_mascota'     => (int)$this->request->getPost('id_mascota'),
            'id_veterinario' => (int)$this->request->getPost('id_veterinario'),
            'fecha_hora'     => $fechaHoraMySQL,
            'motivo'         => trim($this->request->getPost('motivo')),
        ];

        $citaModel = new CitaModel();
        $citaModel->update((int)$id, $data);

        return redirect()->to('/recepcion/citas')->with('success', 'Cita actualizada correctamente.');
    }

    public function cancelar_cita($id)
    {
        $this->requireRecepcionista();

        $citaModel = new CitaModel();
        $cita = $citaModel->find((int)$id);

        if (!$cita) {
            return redirect()->to('/recepcion/citas')->with('error', 'Cita no encontrada.');
        }

        $citaModel->update((int)$id, ['estado' => 0]);

        return redirect()->to('/recepcion/citas')->with('success', 'Cita cancelada correctamente.');
    }

    public function crearUsuario()
    {
        $this->requireRecepcionista();

        return view('recepcion/crear_usuario_cliente', [
            'validation' => Services::validation()
        ]);
    }

public function guardarUsuario()
{
    $this->requireRecepcionista();

    $usuarioModel = new UsuarioModel();

    $rules = [
        'nombre'             => 'required|min_length[3]',
        'correo_electronico' => 'required|valid_email|is_unique[usuarios.correo_electronico]',
        'contrasena'         => 'required|min_length[6]',
        'estado'             => 'permit_empty|in_list[0,1]', // <-- opcional
        'telefono'           => 'permit_empty|string|max_length[20]',
    ];

    if (! $this->validate($rules)) {
        return redirect()->back()->withInput()->with('validation', $this->validator);
    }

    $estado   = (int)($this->request->getPost('estado') ?? 1);
    $telefono = trim((string)$this->request->getPost('telefono'));

    $dataUsuario = [
        'nombre'             => trim($this->request->getPost('nombre')),
        'correo_electronico' => strtolower(trim($this->request->getPost('correo_electronico'))),
        'contrasena'         => password_hash($this->request->getPost('contrasena'), PASSWORD_DEFAULT),
        'id_rol'             => 4, // cliente
        'estado'             => $estado,
    ];

    $db = \Config\Database::connect();
    $db->transStart();

    // 1) Insert usuario
    if (! $usuarioModel->insert($dataUsuario)) {
        $db->transRollback();
        return redirect()->back()
            ->withInput()
            ->with('error', 'No se pudo registrar el usuario.')
            ->with('validation_errors', $usuarioModel->errors());
    }
    $idUsuario = (int)$usuarioModel->getInsertID();
    if ($idUsuario <= 0) {
        $db->transRollback();
        return redirect()->back()->withInput()->with('error', 'No se obtuvo el ID del usuario.');
    }

    // 2) Insert cliente (ajusta campos requeridos por tu tabla)
    $clienteModel = new ClienteModel();
    $dataCliente = [
        'id_usuario' => $idUsuario,
        'nombre'     => $dataUsuario['nombre'],
        'apellido'   => '',
        'telefono'   => $telefono,
        'estado'     => $estado,
        // 'documento' => ...,  // si tu tabla lo exige (NOT NULL / UNIQUE)
        // 'direccion' => ...,  // idem
    ];
    if (! $clienteModel->insert($dataCliente)) {
        // Limpieza: elimina el usuario creado para no dejar huérfanos
        $db->transRollback();
        $usuarioModel->delete($idUsuario, true);
        return redirect()->back()
            ->withInput()
            ->with('error', 'No se pudo registrar el cliente.')
            ->with('validation_errors', $clienteModel->errors());
    }

    $db->transComplete();

    if (! $db->transStatus()) {
        return redirect()->back()->withInput()->with('error', 'Transacción fallida.');
    }

    return redirect()->to(base_url('recepcion/clientes'))
       ->with('ok', 'Cliente registrado correctamente');

}


    public function listarClientes()
    {
        $this->requireRecepcionista();

        $clienteModel = new ClienteModel();

        $clientes = $clienteModel
            ->select('clientes.id_cliente, clientes.nombre, clientes.apellido, clientes.telefono, usuarios.correo_electronico, clientes.estado')
            ->join('usuarios', 'usuarios.id_usuario = clientes.id_usuario')
            ->findAll();

        return view('recepcion/lista_clientes', ['clientes' => $clientes]);
    }

    public function desactivarCliente($id)
    {
        $this->requireRecepcionista();

        $clienteModel = new ClienteModel();
        if (!$clienteModel->find((int)$id)) {
            return redirect()->to('/recepcion/clientes')->with('error', 'Cliente no encontrado.');
        }

        $clienteModel->update((int)$id, ['estado' => 0]);
        return redirect()->to('/recepcion/clientes')->with('success', 'Cliente desactivado correctamente.');
    }

    public function activarCliente($id)
    {
        $this->requireRecepcionista();

        $clienteModel = new ClienteModel();
        if (!$clienteModel->find((int)$id)) {
            return redirect()->to('/recepcion/clientes')->with('error', 'Cliente no encontrado.');
        }

        $clienteModel->update((int)$id, ['estado' => 1]);
        return redirect()->to('/recepcion/clientes')->with('success', 'Cliente activado correctamente.');
    }

    public function crear()
    {
        $this->requireRecepcionista();

        $clienteModel = new ClienteModel();
        $clientes = $clienteModel->where('estado', 1)->findAll();

        return view('recepcion/crear_mascota', compact('clientes'));
    }

    public function guardar()
    {
        $this->requireRecepcionista();

        $mascotaModel = new MascotaModel();

        $data = [
            'nombre'     => trim($this->request->getPost('nombre')),
            'especie'    => trim($this->request->getPost('especie')),
            'raza'       => trim($this->request->getPost('raza')),
            'edad'       => (int)$this->request->getPost('edad'),
            'peso'       => (float)$this->request->getPost('peso'),
            'id_cliente' => (int)$this->request->getPost('id_cliente'),
            'estado'     => (int)$this->request->getPost('estado'),
        ];

        if (!$mascotaModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Error al guardar la mascota: ' . implode(' ', $mascotaModel->errors()));
        }

        return redirect()->to('/recepcion/mascotas')->with('success', 'Mascota registrada correctamente');
    }

    public function listarMascotas()
    {
        $this->requireRecepcionista();

        $db = \Config\Database::connect();
        $mascotas = $db->table('mascotas m')
            ->select('m.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido')
            ->join('clientes c', 'c.id_cliente = m.id_cliente')
            ->get()->getResult();

        return view('recepcion/lista_mascotas', ['mascotas' => $mascotas]);
    }

    public function crearFactura()
    {
        $this->requireRecepcionista();

        helper('form');

        $clienteModel  = new ClienteModel();
        $productoModel = new ProductoModel();
        $servicioModel = new ServicioModel();

        $clientes  = $clienteModel->where('estado', 1)->findAll();
        $productos = $productoModel->where('estado', 1)->findAll();
        $servicios = $servicioModel->where('estado', 1)->findAll();

        return view('recepcion/crear_factura', [
            'clientes'    => $clientes,
            'productos'   => $productos,
            'servicios'   => $servicios,
            'validation'  => Services::validation(),
        ]);
    }

public function guardarFactura()
{
    $this->requireRecepcionista();

    helper(['form']);

    $facturaModel = new FacturaModel();
    $detalleModel = new DetalleFacturaModel();

    // Datos de cabecera
    $idCliente = (int)$this->request->getPost('id_cliente');
    $fecha     = $this->request->getPost('fecha'); // Y-m-d
    $estado    = (int)$this->request->getPost('estado');

    // ===== CAMBIO: ahora el descuento viene en PORCENTAJE =====
    $descuentoPct = (float)($this->request->getPost('descuento') ?? 0); // % 0..100
    // Si deseas permitir editar IVA desde el form, léelo del POST; si no, déjalo fijo:
    $ivaPorcentaje = 13.0; // o: (float)($this->request->getPost('iva_porcentaje') ?? 13)

    // Detalles (arrays)
    $productos  = (array)$this->request->getPost('id_producto');
    $servicios  = (array)$this->request->getPost('id_servicio');
    $cantidades = (array)$this->request->getPost('cantidad');
    $precios    = (array)$this->request->getPost('precio_unitario');

    // --- Validación de cabecera mínima ---
    $rules = [
        'id_cliente' => 'required|is_natural_no_zero',
        'fecha'      => 'required|valid_date[Y-m-d]',
        'estado'     => 'required|in_list[0,1]',
        // CAMBIO: valida descuento como porcentaje (opcional pero recomendado)
        // 'descuento' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
    ];
    if (!$this->validate($rules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    // --- Normalización y validación de detalles ---
    $detalles = [];
    $n = max(count($productos), count($servicios), count($cantidades), count($precios));

    for ($i = 0; $i < $n; $i++) {
        $idProd = $productos[$i] ?? null;
        $idServ = $servicios[$i] ?? null;
        $cant   = (float)($cantidades[$i] ?? 0);
        $precio = (float)($precios[$i] ?? 0);

        // Reglas: exactamente uno elegido (XOR), cantidad>0, precio>0
        $unoElegido = (!empty($idProd) xor !empty($idServ));
        if (!$unoElegido || $cant <= 0 || $precio <= 0) {
            continue; // ignora filas inválidas
        }

        $detalles[] = [
            'id_producto'     => $idProd ?: null,
            'id_servicio'     => $idServ ?: null,
            'cantidad'        => $cant,
            'precio_unitario' => round($precio, 2),
            'subtotal'        => round($cant * $precio, 2),
        ];
    }

    if (empty($detalles)) {
        return redirect()->back()->withInput()->with('errors', ['Debe agregar al menos un detalle válido.']);
    }

    // --- Cálculos financieros (DESCUENTO COMO %) ---
    $subtotal = array_reduce($detalles, fn($acc, $d) => $acc + $d['subtotal'], 0.0);
    $subtotal = round($subtotal, 2);

    // Clamp 0..100 y monto de descuento
    $descuentoPct = max(0.0, min(100.0, round($descuentoPct, 2)));
    $descuentoMonto = round($subtotal * ($descuentoPct / 100), 2);

    $base  = max(round($subtotal - $descuentoMonto, 2), 0.0);
    $iva   = round($base * ($ivaPorcentaje / 100), 2);
    $total = round($base + $iva, 2);

    $db = \Config\Database::connect();
    $db->transStart();

    // Inserta factura
    // Si tu tabla guarda el MONTO de descuento, deja 'descuento' => $descuentoMonto.
    // Si prefieres guardar el PORCENTAJE, crea/usa 'descuento_pct' => $descuentoPct.
    $facturaData = [
        'id_cliente' => $idCliente,
        'fecha'      => $fecha,
        'descuento'  => $descuentoMonto,   // <-- MONTO aplicado
        // 'descuento_pct' => $descuentoPct, // <-- alternativa si guardas el %
        'iva'        => $iva,
        'total'      => $total,
        'estado'     => $estado,
    ];

    $facturaId = $facturaModel->insert($facturaData, true);

    if (!$facturaId) {
        $db->transRollback();
        return redirect()->back()->withInput()->with('errors', ['No se pudo guardar la factura.']);
    }

    foreach ($detalles as $d) {
        $d['id_factura'] = $facturaId;
        if (!$detalleModel->insert($d)) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('errors', ['No se pudo guardar un detalle de la factura.']);
        }
    }

    $db->transComplete();

    if (!$db->transStatus()) {
        return redirect()->back()->withInput()->with('errors', ['Transacción fallida al registrar la factura.']);
    }

    return redirect()->to('recepcion/facturas')->with('success', 'Factura registrada con éxito.');
}


public function detalleFactura($id)
{
    $this->requireRecepcionista();

    $id = (int)$id;
    if ($id <= 0) {
        return redirect()->to(base_url('recepcion/facturas'))->with('error', 'ID de factura inválido.');
    }

    $db = \Config\Database::connect();

    // Cabecera de factura + cliente
    $factura = $db->table('facturas f')
        ->select('f.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido')
        ->join('clientes c', 'c.id_cliente = f.id_cliente')
        ->where('f.id_factura', $id)
        ->get()->getRow();

    if (!$factura) {
        return redirect()->to(base_url('recepcion/facturas'))->with('error', 'Factura no encontrada.');
    }

    // Detalles con descripción unificada (producto o servicio)
    $detalles = $db->table('detalles_factura d')
        ->select("
            d.*,
            p.nombre AS producto,
            s.nombre AS servicio,
            COALESCE(p.nombre, s.nombre, '-') AS descripcion
        ")
        ->join('productos p', 'p.id_producto = d.id_producto', 'left')
        ->join('servicios s', 's.id_servicio = d.id_servicio', 'left')
        ->where('d.id_factura', $id)
        ->orderBy('d.id_detalle', 'ASC')
        ->get()->getResult();

    // —— Resumen financiero (robusto a columnas faltantes) ——
    $subtotal = 0.0;
    foreach ($detalles as $d) {
        $subtotal += (float)($d->subtotal ?? 0);
    }
    $subtotal = round($subtotal, 2);

    // Si tu tabla ya tiene descuento/iva, se usan; si no, asumimos 0 y calculamos 13%
    $descuento = property_exists($factura, 'descuento') && $factura->descuento !== null
        ? (float)$factura->descuento : 0.0;

    $ivaGuardado = property_exists($factura, 'iva') && $factura->iva !== null
        ? (float)$factura->iva : null;

    $ivaPorcentaje = 13.0; // Ajusta si cambia el % en tu negocio
    $base = max($subtotal - $descuento, 0.0);
    $ivaCalculado = round($base * ($ivaPorcentaje / 100), 2);
    $iva = $ivaGuardado !== null ? $ivaGuardado : $ivaCalculado;

    $totalCalc = round($base + $iva, 2);
    $totalBD   = round((float)$factura->total, 2);
    $coincide  = (abs($totalBD - $totalCalc) < 0.005);

    $resumen = [
        'subtotal'      => $subtotal,
        'descuento'     => $descuento,
        'iva'           => $iva,
        'iva_porcentaje'=> $ivaPorcentaje,
        'total_calc'    => $totalCalc,
        'total_bd'      => $totalBD,
        'coincide'      => $coincide,
    ];

    // Pasa también una lista plana con 'descripcion' para simplificar la vista
    return view('recepcion/detalle_factura', [
        'factura'  => $factura,
        'detalles' => $detalles,
        'resumen'  => $resumen,
    ]);
}

    public function listarFacturas()
    {
        $this->requireRecepcionista();

        $db = \Config\Database::connect();
        $facturas = $db->table('facturas f')
            ->select('f.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido')
            ->join('clientes c', 'c.id_cliente = f.id_cliente')
            ->orderBy('f.fecha', 'DESC')
            ->get()->getResult();

        return view('recepcion/lista_facturas', ['facturas' => $facturas]);
    }

// public function descargar_pdf($id)
// {
//     $this->requireRecepcionista();

//     // 1) Validación de ID
//     $id = (int)$id;
//     if ($id <= 0) {
//         return redirect()->to(base_url('recepcion/facturas'))
//             ->with('error', 'ID inválido.');
//     }

//     $db = \Config\Database::connect();

//     // 2) Cabecera de la factura
//     $factura = $db->table('facturas f')
//         ->select('f.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido')
//         ->join('clientes c', 'c.id_cliente = f.id_cliente')
//         ->where('f.id_factura', $id)
//         ->get()
//         ->getRow();

//     if (!$factura) {
//         return redirect()->to(base_url('recepcion/facturas'))
//             ->with('error', 'Factura no encontrada.');
//     }

//     // 3) Detalle de líneas
//     $detalles = $db->table('detalles_factura d')
//         ->select("
//             d.*,
//             p.nombre AS producto,
//             s.nombre AS servicio,
//             COALESCE(p.nombre, s.nombre, '-') AS descripcion
//         ")
//         ->join('productos p', 'p.id_producto = d.id_producto', 'left')
//         ->join('servicios s', 's.id_servicio = d.id_servicio', 'left')
//         ->where('d.id_factura', $id)
//         ->orderBy('d.id_detalle', 'ASC')
//         ->get()
//         ->getResult();

//     // 4) Cálculos de resumen
//     // 4) Cálculos de resumen (robustos)
//     $subtotal = 0.0;
//     foreach ($detalles as $d) {
//         // Usa d->subtotal si existe; si no, calcula cantidad * precio_unitario (o precio)
//         $cant = (float)($d->cantidad ?? 0);
//         $precioUnit = (float)($d->precio_unitario ?? ($d->precio ?? 0));
//         $linea = isset($d->subtotal) ? (float)$d->subtotal : ($cant * $precioUnit);
//         $subtotal += $linea;
//     }
//     $subtotal = round($subtotal, 2);

//     // Normaliza descuento
//     $descuento = (property_exists($factura, 'descuento') && $factura->descuento !== null)
//         ? (float)$factura->descuento : 0.0;
//     if ($descuento < 0) $descuento = 0.0;
//     if ($descuento > $subtotal) $descuento = $subtotal;

//     // IVA: porcentaje y posible exención (si tienes esas columnas, si no quedan por defecto)
//     $ivaPct   = property_exists($factura, 'iva_pct') && $factura->iva_pct !== null
//         ? (float)$factura->iva_pct : 13.0;
//     $exento   = property_exists($factura, 'exento') ? (bool)$factura->exento : false;

//     // Base imponible
//     $base   = max($subtotal - $descuento, 0.0);

//     // IVA calculado
//     $ivaCalc = $exento ? 0.0 : round($base * ($ivaPct / 100), 2);

//     // IVA guardado (si viene 0.00 y no es exento, lo tratamos como “no calculado”)
//     $ivaGuard = (property_exists($factura, 'iva') && $factura->iva !== null)
//         ? (float)$factura->iva : null;

//     $iva = ($ivaGuard === null || (!$exento && abs($ivaGuard) < 0.005))
//         ? $ivaCalc
//         : ($exento ? 0.0 : round($ivaGuard, 2));

//     // Totales
//     $totalCalc = round($base + $iva, 2);
//     $totalBD   = round((float)($factura->total ?? $totalCalc), 2);


//     // 5) Datos empresa
//     $empresa = [
//         'nombre'    => 'Clínica UH',
//         'direccion' => 'Puntarenas, CR',
//         'telefono'  => '8509-1993',
//         'correo'    => 'info@clinicauh.cr',
//     ];

//     // 6) Leer logo base64 (robusto)
//     $logoBase64 = '';
//     $candidatos = [
//         WRITEPATH . 'veterinaria_uh_base64.txt',
//         WRITEPATH . 'veterinaria_uh_base64', // por si Windows oculta la extensión
//     ];
//     foreach ($candidatos as $p) {
//         if (is_file($p)) {
//             $logoBase64 = @file_get_contents($p);
//             if ($logoBase64 !== false) break;
//         }
//     }
//     // Limpiar contenido: quitar prefijo data: y espacios/saltos
//     $logoBase64 = trim((string)$logoBase64);
//     $logoBase64 = preg_replace('/^data:image\/(png|jpe?g);base64,/i', '', $logoBase64);
//     $logoBase64 = preg_replace('/\s+/', '', $logoBase64);
//     if ($logoBase64 === '') {
//         log_message('error', 'Logo base64 vacío o no encontrado en writable/.');
//     }

//     $resumen = [
//         'subtotal'       => $subtotal,
//         'descuento'      => $descuento,
//         'iva'            => $iva,
//         'iva_porcentaje' => $ivaPct,
//         'total_calc'     => $totalCalc,
//         'total_bd'       => $totalBD,
//         'coincide'       => (abs($totalBD - $totalCalc) < 0.005),
//     ];

//     // 7) Render de vista específica para PDF (sin layout)
//     $html = view('recepcion/detalle_factura_pdf', [
//         'empresa'     => $empresa,
//         'logoBase64'  => $logoBase64,   
//         'factura'     => $factura,
//         'detalles'    => $detalles,
//         'resumen'     => $resumen,
//     ]);

//     // 8) Generación del PDF con Dompdf
//     $options = new Options();
//     $options->set('isHtml5ParserEnabled', true);
//     $options->set('isRemoteEnabled', true);     
//     $options->set('defaultFont', 'DejaVu Sans'); 
//     $options->set('dpi', 96);

//     $dompdf = new Dompdf($options);
//     $dompdf->loadHtml($html, 'UTF-8');
//     $dompdf->setPaper('A4', 'portrait');        
//     $dompdf->render();

//     // 9) Respuesta
//     $filename = 'factura_' . $id . '.pdf';
//     return $this->response
//         ->setContentType('application/pdf')
//         ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
//         ->setBody($dompdf->output());
// }

    public function descargar_pdf($id)
{
    $this->requireRecepcionista();

    // 1) Validación de ID
    $id = (int)$id;
    if ($id <= 0) {
        return redirect()->to(base_url('recepcion/facturas'))
            ->with('error', 'ID inválido.');
    }

    $db = \Config\Database::connect();

    // 2) Cabecera de la factura (+ cliente)
    $factura = $db->table('facturas f')
        ->select('f.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido')
        ->join('clientes c', 'c.id_cliente = f.id_cliente')
        ->where('f.id_factura', $id)
        ->get()
        ->getRow();

    if (!$factura) {
        return redirect()->to(base_url('recepcion/facturas'))
            ->with('error', 'Factura no encontrada.');
    }

    // 3) Detalle de líneas
    $detalles = $db->table('detalles_factura d')
        ->select("
            d.*,
            p.nombre AS producto,
            s.nombre AS servicio,
            COALESCE(p.nombre, s.nombre, '-') AS descripcion
        ")
        ->join('productos p', 'p.id_producto = d.id_producto', 'left')
        ->join('servicios s', 's.id_servicio = d.id_servicio', 'left')
        ->where('d.id_factura', $id)
        ->orderBy('d.id_detalle', 'ASC')
        ->get()
        ->getResult();

    // 4) Subtotal robusto (usa subtotal de línea si existe, si no cant * precio_unitario)
    $subtotal = 0.0;
    foreach ($detalles as $d) {
        $cant = (float)($d->cantidad ?? 0);
        $pu   = (float)($d->precio_unitario ?? ($d->precio ?? 0));
        $linea = isset($d->subtotal) ? (float)$d->subtotal : ($cant * $pu);
        $subtotal += $linea;
    }
    $subtotal = round($subtotal, 2);

    // 5) Descuento: soporta monto (descuento) y/o porcentaje (descuento_pct)
    $descuentoMonto = (property_exists($factura, 'descuento') && $factura->descuento !== null)
        ? (float)$factura->descuento : 0.0;

    $descuentoPct = 0.0;
    if (property_exists($factura, 'descuento_pct') && $factura->descuento_pct !== null) {
        // Si ya existe porcentaje en BD, úsalo
        $descuentoPct = (float)$factura->descuento_pct;
        // Si el monto guardado parece 0 pero hay %, recalcula el monto para el PDF
        if ($descuentoMonto <= 0 && $subtotal > 0 && $descuentoPct > 0) {
            $descuentoMonto = round($subtotal * ($descuentoPct / 100), 2);
        }
    } else {
        // Si NO hay porcentaje en BD, calcúlalo desde el monto
        $descuentoPct = ($subtotal > 0)
            ? round(($descuentoMonto / $subtotal) * 100, 2)
            : 0.0;
    }

    // Normalizaciones (nunca negativos, ni mayores al subtotal)
    if ($descuentoMonto < 0) $descuentoMonto = 0.0;
    if ($descuentoMonto > $subtotal) $descuentoMonto = $subtotal;
    // Clamp del porcentaje por prolijidad
    $descuentoPct = max(0.0, min(100.0, $descuentoPct));

    // 6) IVA / exención
    $ivaPct = property_exists($factura, 'iva_pct') && $factura->iva_pct !== null
        ? (float)$factura->iva_pct
        : 13.0;

    $exento = property_exists($factura, 'exento') ? (bool)$factura->exento : false;

    // 7) Base imponible, IVA y total
    $base = max(round($subtotal - $descuentoMonto, 2), 0.0);

    $ivaCalc = $exento ? 0.0 : round($base * ($ivaPct / 100), 2);

    $ivaGuard = (property_exists($factura, 'iva') && $factura->iva !== null)
        ? (float)$factura->iva : null;

    $iva = ($ivaGuard === null || (!$exento && abs($ivaGuard) < 0.005))
        ? $ivaCalc
        : ($exento ? 0.0 : round($ivaGuard, 2));

    $totalCalc = round($base + $iva, 2);
    $totalBD   = round((float)($factura->total ?? $totalCalc), 2);

    // 8) Datos empresa (ajústalo a tu realidad)
    $empresa = [
        'nombre'    => 'Clínica UH',
        'direccion' => 'Puntarenas, CR',
        'telefono'  => '8509-1993',
        'correo'    => 'info@clinicauh.cr',
    ];

    // 9) Logo base64 (desde writable/)
    $logoBase64 = '';
    $candidatos = [
        WRITEPATH . 'veterinaria_uh_base64.txt',
        WRITEPATH . 'veterinaria_uh_base64',
    ];
    foreach ($candidatos as $p) {
        if (is_file($p)) {
            $logoBase64 = @file_get_contents($p);
            if ($logoBase64 !== false) break;
        }
    }
    $logoBase64 = trim((string)$logoBase64);
    $logoBase64 = preg_replace('/^data:image\/(png|jpe?g);base64,/i', '', $logoBase64);
    $logoBase64 = preg_replace('/\s+/', '', $logoBase64);

    // 10) Paquete para la vista (ahora con descuento_pct)
    $resumen = [
        'subtotal'        => $subtotal,
        'descuento'       => $descuentoMonto,   // MONTO aplicado
        'descuento_pct'   => $descuentoPct,     // % para mostrar
        'iva'             => $iva,
        'iva_porcentaje'  => $ivaPct,
        'total_calc'      => $totalCalc,
        'total_bd'        => $totalBD,
        'coincide'        => (abs($totalBD - $totalCalc) < 0.005),
        'exento'          => $exento,
    ];

    // 11) Render de vista (sin layout)
    $html = view('recepcion/detalle_factura_pdf', [
        'empresa'     => $empresa,
        'logoBase64'  => $logoBase64,
        'factura'     => $factura,
        'detalles'    => $detalles,
        'resumen'     => $resumen,
    ]);

    // 12) Generación PDF (Dompdf)
    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('dpi', 96);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 13) Respuesta
    $filename = 'factura_' . $id . '.pdf';
    return $this->response
        ->setContentType('application/pdf')
        ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
        ->setBody($dompdf->output());
}


}
