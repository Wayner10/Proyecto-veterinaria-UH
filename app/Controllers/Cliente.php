<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\MascotaModel;
use App\Models\CitaModel;
use App\Models\FacturaModel;
use App\Models\DetalleFacturaModel;
use App\Models\ProductoModel;
use App\Models\ServicioModel;
use App\Models\UsuarioModel;
use CodeIgniter\I18n\Time;

class Cliente extends BaseController
{
    private string $tz = 'America/Costa_Rica';

    /** URL de login real */
    private function loginUrl(): string
    {
        return base_url('auth/login');
    }

    /** Exige sesión y rol de cliente (4) */
    private function requireCliente(): void
    {
        if (!session()->get('logged_in') || (int) session()->get('id_rol') !== 4) {
            redirect()->to($this->loginUrl())->send();
            exit;
        }
    }

    /** Devuelve el id_cliente a partir del id_usuario logueado; lanza redirect si no hay relación */
    private function resolveClienteIdOrFail(): int
    {
        $idUsuario = (int) (session()->get('id_usuario') ?? 0);
        if ($idUsuario <= 0) {
            redirect()->to($this->loginUrl())->send();
            exit;
        }
        $cliente = (new ClienteModel())->where('id_usuario', $idUsuario)->first();
        if (!$cliente) {
            redirect()->to($this->loginUrl())->with('error', 'No se encontró el cliente asociado.')->send();
            exit;
        }
        return (int) $cliente['id_cliente'];
    }

    // ======= Dashboard =======
    public function inicio()
    {
        $this->requireCliente();
        $idCliente = $this->resolveClienteIdOrFail();

        $hoy     = Time::today($this->tz)->toDateString();

        $citaM   = new CitaModel();
        $mascM   = new MascotaModel();
        $factM   = new FacturaModel();

        $mascotas = $mascM->where('id_cliente', $idCliente)->countAllResults();

        // Citas futuras (>= hoy)
        $citasPend = $citaM->select('citas.*')
            ->join('mascotas', 'mascotas.id_mascota = citas.id_mascota')
            ->where('mascotas.id_cliente', $idCliente)
            ->where('fecha_hora >=', $hoy . ' 00:00:00')
            ->countAllResults();

        $facturas = $factM->where('id_cliente', $idCliente)->countAllResults();

        return view('cliente/inicio', compact('mascotas', 'citasPend', 'facturas'));
    }

    // ======= Mis Mascotas =======
    public function mascotas()
    {
        $this->requireCliente();
        $idCliente = $this->resolveClienteIdOrFail();

        $mascotas = (new MascotaModel())
            ->where('id_cliente', $idCliente)
            ->orderBy('nombre', 'ASC')
            ->findAll();

        return view('cliente/mascotas', compact('mascotas'));
    }

    // ======= Mis Citas =======
    public function citas()
    {
        $this->requireCliente();
        $idCliente = $this->resolveClienteIdOrFail();

        $db = \Config\Database::connect();
        $citas = $db->table('citas c')
            ->select('c.id_cita, c.fecha_hora, c.estado, c.motivo,
                      m.nombre AS mascota,
                      v.nombre AS veterinario, v.apellido AS vet_apellido')
            ->join('mascotas m', 'm.id_mascota = c.id_mascota')
            ->join('veterinarios v', 'v.id_veterinario = c.id_veterinario')
            ->where('m.id_cliente', $idCliente)
            ->orderBy('c.fecha_hora', 'DESC')
            ->get()->getResult();

        return view('cliente/citas', compact('citas'));
    }

    // (Opcional) Solicitar nueva cita por el cliente
    public function solicitarCita()
    {
        $this->requireCliente();
        $idCliente = $this->resolveClienteIdOrFail();

        $db = \Config\Database::connect();
        $mascotas = $db->table('mascotas')->where('id_cliente', $idCliente)->where('estado', 1)->get()->getResult();
        $veterinarios = $db->table('veterinarios')->where('estado', 1)->get()->getResult();

        return view('cliente/solicitar_cita', compact('mascotas', 'veterinarios'));
    }

    public function guardarSolicitudCita()
    {
        $this->requireCliente();
        $idCliente = $this->resolveClienteIdOrFail();

        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_mascota'     => 'required|is_natural_no_zero',
            'id_veterinario' => 'required|is_natural_no_zero',
            'fecha_hora'     => 'required|valid_date[Y-m-d\TH:i]',
            'motivo'         => 'required|min_length[3]|max_length[255]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Validar que la mascota pertenezca al cliente
        $idMascota = (int)$this->request->getPost('id_mascota');
        $own = (new MascotaModel())->where('id_mascota', $idMascota)->where('id_cliente', $idCliente)->first();
        if (!$own) {
            return redirect()->back()->withInput()->with('errors', ['La mascota seleccionada no pertenece a su cuenta.']);
        }

        $fechaHoraMySQL = str_replace('T', ' ', $this->request->getPost('fecha_hora')) . ':00';

        $ok = (new CitaModel())->insert([
            'id_mascota'     => $idMascota,
            'id_veterinario' => (int)$this->request->getPost('id_veterinario'),
            'fecha_hora'     => $fechaHoraMySQL,
            'motivo'         => trim($this->request->getPost('motivo')),
            'estado'         => 1,
        ]);

        if (!$ok) {
            return redirect()->back()->withInput()->with('error', 'No fue posible registrar la cita.');
        }

        return redirect()->to('/cliente/citas')->with('success', 'Cita solicitada con éxito.');
    }

    // ======= Mis Facturas =======
    public function facturas()
    {
        $this->requireCliente();
        $idCliente = $this->resolveClienteIdOrFail();

        $facturas = (new FacturaModel())
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha', 'DESC')
            ->findAll();

        return view('cliente/facturas', compact('facturas'));
    }

    public function detalleFactura($id)
    {
        $this->requireCliente();
        $idCliente = $this->resolveClienteIdOrFail();

        $db = \Config\Database::connect();

        $factura = $db->table('facturas f')
            ->select('f.*')
            ->where('f.id_factura', (int)$id)
            ->where('f.id_cliente', $idCliente) // seguridad: solo sus facturas
            ->get()->getRow();

        if (!$factura) {
            return redirect()->to('/cliente/facturas')->with('error', 'Factura no encontrada.');
        }

        $detalles = $db->table('detalles_factura d')
            ->select("
                d.*,
                p.nombre AS producto,
                s.nombre AS servicio,
                COALESCE(p.nombre, s.nombre, '-') AS descripcion
            ")
            ->join('productos p', 'p.id_producto = d.id_producto', 'left')
            ->join('servicios s', 's.id_servicio = d.id_servicio', 'left')
            ->where('d.id_factura', (int)$id)
            ->orderBy('d.id_detalle', 'ASC')
            ->get()->getResult();

        // Resumen (usa columnas si existen; si no, calcula)
        $subtotal = 0.0;
        foreach ($detalles as $d) { $subtotal += (float)($d->subtotal ?? 0); }
        $subtotal = round($subtotal, 2);

        $descuento = property_exists($factura, 'descuento') && $factura->descuento !== null ? (float)$factura->descuento : 0.0;
        $ivaGuard  = property_exists($factura, 'iva') && $factura->iva !== null ? (float)$factura->iva : null;
        $ivaPct    = 13.0;

        $base    = max($subtotal - $descuento, 0.0);
        $ivaCalc = round($base * ($ivaPct / 100), 2);
        $iva     = $ivaGuard !== null ? $ivaGuard : $ivaCalc;

        $totalCalc = round($base + $iva, 2);
        $totalBD   = round((float)$factura->total, 2);
        $coincide  = (abs($totalBD - $totalCalc) < 0.005);

        $resumen = [
            'subtotal'        => $subtotal,
            'descuento'       => $descuento,
            'iva'             => $iva,
            'iva_porcentaje'  => $ivaPct,
            'total_calc'      => $totalCalc,
            'total_bd'        => $totalBD,
            'coincide'        => $coincide,
        ];

        return view('cliente/detalle_factura', [
            'factura'  => $factura,
            'detalles' => $detalles,
            'resumen'  => $resumen,
        ]);
    }

    public function descargar_pdf($id)
    {
        $this->requireCliente();
        $idCliente = $this->resolveClienteIdOrFail();

        $id = (int)$id;
        if ($id <= 0) {
            return redirect()->to('/cliente/facturas')->with('error', 'ID inválido.');
        }

        $db = \Config\Database::connect();

        $factura = $db->table('facturas f')
            ->select('f.*, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido')
            ->join('clientes c', 'c.id_cliente = f.id_cliente')
            ->where('f.id_factura', $id)
            ->where('f.id_cliente', $idCliente) // seguridad
            ->get()->getRow();

        if (!$factura) {
            return redirect()->to('/cliente/facturas')->with('error', 'Factura no encontrada.');
        }

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

        // Resumen
        $subtotal = 0.0;
        foreach ($detalles as $d) { $subtotal += (float)($d->subtotal ?? 0); }
        $subtotal = round($subtotal, 2);

        $descuento = property_exists($factura, 'descuento') && $factura->descuento !== null ? (float)$factura->descuento : 0.0;
        $ivaGuard  = property_exists($factura, 'iva') && $factura->iva !== null ? (float)$factura->iva : null;
        $ivaPct    = 13.0;

        $base    = max($subtotal - $descuento, 0.0);
        $ivaCalc = round($base * ($ivaPct / 100), 2);
        $iva     = $ivaGuard !== null ? $ivaGuard : $ivaCalc;

        $totalCalc = round($base + $iva, 2);
        $totalBD   = round((float)$factura->total, 2);

        $resumen = [
            'subtotal'        => $subtotal,
            'descuento'       => $descuento,
            'iva'             => $iva,
            'iva_porcentaje'  => $ivaPct,
            'total_calc'      => $totalCalc,
            'total_bd'        => $totalBD,
            'coincide'        => (abs($totalBD - $totalCalc) < 0.005),
        ];
  
        $totalParaMostrar = $resumen['total_calc']; // usa el total con IVA calculado

        $html = view('recepcion/detalle_factura_pdf', [
            'factura'  => $factura,
            'detalles' => $detalles,
            'resumen'  => $resumen,
            'totalParaMostrar' => $totalParaMostrar, 
        ]);


        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'factura_' . $id . '.pdf';
        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output())
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    // ======= Perfil del cliente =======
    public function perfil()
    {
        $this->requireCliente();
        $idUsuario = (int) session()->get('id_usuario');

        $usuario = (new UsuarioModel())->find($idUsuario);
        $cliente = (new ClienteModel())->where('id_usuario', $idUsuario)->first();

        return view('cliente/perfil', compact('usuario', 'cliente'));
    }

    public function actualizarPerfil()
    {
        $this->requireCliente();
        $idUsuario = (int) session()->get('id_usuario');

        $val = \Config\Services::validation();
        $val->setRules([
            'nombre'       => 'required|min_length[3]',
            'telefono'     => 'permit_empty|max_length[20]',
            'contrasena'   => 'permit_empty|min_length[6]',
        ]);

        if (!$val->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $val->getErrors());
        }

        $nombre     = trim($this->request->getPost('nombre'));
        $telefono   = trim((string)$this->request->getPost('telefono'));
        $contrasena = $this->request->getPost('contrasena');

        $uModel = new UsuarioModel();
        $cModel = new ClienteModel();

        $uData = ['nombre' => $nombre];
        if (!empty($contrasena)) {
            $uData['contrasena'] = password_hash($contrasena, PASSWORD_DEFAULT);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $uModel->update($idUsuario, $uData);
        $cliente = $cModel->where('id_usuario', $idUsuario)->first();
        if ($cliente) {
            $cModel->update((int)$cliente['id_cliente'], ['nombre' => $nombre, 'telefono' => $telefono]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'No fue posible actualizar el perfil.');
        }

        return redirect()->to('/cliente/perfil')->with('success', 'Perfil actualizado.');
    }
}
