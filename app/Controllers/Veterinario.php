<?php

namespace App\Controllers;

use App\Models\CitaModel;
use App\Models\TratamientoModel;
use App\Models\MascotaModel;

class Veterinario extends BaseController
{
    /** Requiere sesión y rol 2 (veterinario) */
    private function requireVeterinario(): void
    {
        if (!session()->get('logged_in') || (int) session()->get('id_rol') !== 2) {
            redirect()->to(base_url('auth/login'))->send();
            exit;
        }
    }

    /** Lee id_veterinario desde la sesión (debe setearse al iniciar sesión del rol 2) */
    private function resolveVeterinarioIdOrFail(): int
    {
        $idVet = (int) (session()->get('id_veterinario') ?? 0);
        if ($idVet <= 0) {
            redirect()
                ->to(base_url('auth/login'))
                ->with('error', 'No se encontró tu perfil de veterinario en la sesión. Asegúrate de que el login setee id_veterinario.')
                ->send();
            exit;
        }
        return $idVet;
    }

    /** Verifica si la tabla tiene una columna (para soportar estado_clinico opcional) */
    private function hasColumn(string $table, string $column): bool
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames($table);
        return in_array($column, $fields, true);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('auth/login'))->with('success', 'Sesión cerrada.');
    }


    // ===================== DASHBOARD =====================
    public function inicio()
    {
        $this->requireVeterinario();
        $idVet = $this->resolveVeterinarioIdOrFail();

        $db = \Config\Database::connect();

        $hoy    = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));

        // Citas de hoy (rango [hoy, mañana))
        $citasHoy = $db->table('citas')
            ->where('id_veterinario', $idVet)
            ->where('fecha_hora >=', $hoy . ' 00:00:00')
            ->where('fecha_hora <',  $manana . ' 00:00:00')
            ->countAllResults();

        // Citas pendientes desde hoy en adelante
        $pendientes = $db->table('citas')
            ->where('id_veterinario', $idVet)
            ->where('fecha_hora >=', $hoy . ' 00:00:00')
            ->countAllResults();

        return view('veterinario/inicio', compact('citasHoy', 'pendientes'));
    }

    // ===================== AGENDA (SOLO LECTURA) =====================
public function ver_citas()
{
    $this->requireVeterinario();
    $idVet = $this->resolveVeterinarioIdOrFail();

    $db = \Config\Database::connect();

    // Determina una sola vez si existe columna estado_clinico
    $tieneEstadoClinico = $this->hasColumn('citas', 'estado_clinico');

    
    $select = 'c.id_cita, c.fecha_hora, c.motivo, c.estado, c.id_veterinario, m.id_mascota, m.nombre AS mascota';
    if ($tieneEstadoClinico) {
        $select .= ', c.estado_clinico';
    }

$citas = $db->table('citas c')
    ->select('
        c.id_cita,
        c.id_veterinario,
        m.id_mascota,
        m.nombre AS nombre_mascota,
        CONCAT(v.nombre, " ", v.apellido) AS nombre_veterinario,
        c.fecha_hora,
        c.motivo,
        c.estado
    ')
    ->join('mascotas m', 'c.id_mascota = m.id_mascota')
    ->join('veterinarios v', 'c.id_veterinario = v.id_veterinario')
    ->where('c.id_veterinario', $idVet)
    ->where('c.estado', 1)
    ->orderBy('c.fecha_hora', 'DESC')
    ->get()
    ->getResult();


    return view('veterinario/citas', [
        'citas'              => $citas,
        'tieneEstadoClinico' => $tieneEstadoClinico,
    ]);
}


    // ===================== ESTADO CLÍNICO =====================
    /** Cambiar estado clínico de la cita (no toca la agenda). */
    public function cambiar_estado($id, $nuevo)
    {
        $this->requireVeterinario();
        $idVet = $this->resolveVeterinarioIdOrFail();

        // Si tu tabla no tiene estado_clinico, no hacemos nada
        if (!$this->hasColumn('citas', 'estado_clinico')) {
            return redirect()
                ->to('/veterinario/citas')
                ->with('error', 'No está habilitado el estado clínico en la configuración actual.');
        }

        $permitidos = ['en_consulta', 'completada', 'no_show'];
        if (!in_array($nuevo, $permitidos, true)) {
            return redirect()
                ->to('/veterinario/citas')
                ->with('error', 'Estado clínico no permitido.');
        }

        $citaM = new CitaModel();
        $cita  = $citaM->find((int) $id);

        if (!$cita || (int) $cita['id_veterinario'] !== $idVet) {
            return redirect()
                ->to('/veterinario/citas')
                ->with('error', 'No autorizado.');
        }

        $citaM->update((int) $id, ['estado_clinico' => $nuevo]);

        return redirect()
            ->to('/veterinario/citas')
            ->with('success', 'Estado clínico actualizado.');
    }

    public function iniciar_consulta($id)   { return $this->cambiar_estado($id, 'en_consulta'); }
    public function completar_consulta($id) { return $this->cambiar_estado($id, 'completada'); }
    public function marcar_no_show($id)     { return $this->cambiar_estado($id, 'no_show'); }

    // ===================== TRATAMIENTOS =====================
    /** Historial de tratamientos por mascota (lectura). */
    public function ver_tratamientos($id_mascota)
{
    $this->requireVeterinario();
    $idVet = $this->resolveVeterinarioIdOrFail();
    $id_mascota = (int) $id_mascota;

    $db = \Config\Database::connect();

    $mascota = $db->table('mascotas')->where('id_mascota', $id_mascota)->get()->getRowArray();

    $tratamientos = $db->table('tratamientos')
        ->where('id_mascota', $id_mascota)
        ->orderBy('id_tratamiento', 'DESC')
        ->get()
        ->getResultArray();

    
    $citaUltima = $db->table('citas')
        ->select('id_cita')
        ->where('id_mascota', $id_mascota)
        ->where('id_veterinario', $idVet)
        ->orderBy('fecha_hora', 'DESC')
        ->get()
        ->getRow();

    $id_cita_ult = $citaUltima->id_cita ?? null;

    return view('veterinario/historial_tratamientos', [
        'mascota'        => $mascota,
        'tratamientos'   => $tratamientos,
        'id_cita_ult'    => $id_cita_ult,   // <-- pásalo a la vista
    ]);
}


    /** Form de tratamiento ligado a una cita (mejor traza clínica). */
    public function crear_tratamiento($id_cita)
    {
        $this->requireVeterinario();
        $idVet   = $this->resolveVeterinarioIdOrFail();
        $id_cita = (int) $id_cita;

        $citaM = new CitaModel();
        $cita  = $citaM->find($id_cita);

        if (!$cita || (int) $cita['id_veterinario'] !== $idVet) {
            return redirect()
                ->to('/veterinario/citas')
                ->with('error', 'No autorizado o cita inexistente.');
        }

        $mascota = (new MascotaModel())->find((int) $cita['id_mascota']);

        return view('veterinario/crear_tratamiento', [
            'cita'    => $cita,
            'mascota' => $mascota,
        ]);
    }

    /** Guarda tratamiento de una consulta (no modifica agenda). */
    public function guardar_tratamiento($id_cita)
    {
        $this->requireVeterinario();
        $idVet   = $this->resolveVeterinarioIdOrFail();
        $id_cita = (int) $id_cita;

        $citaM = new CitaModel();
        $cita  = $citaM->find($id_cita);

        if (!$cita || (int) $cita['id_veterinario'] !== $idVet) {
            return redirect()
                ->to('/veterinario/citas')
                ->with('error', 'No autorizado o cita inexistente.');
        }

        $tratM = new TratamientoModel();

        
        $data = [
            'id_mascota'   => (int) $cita['id_mascota'],
            // 'id_cita'    => $id_cita,        
            // 'id_veterinario' => $idVet,
            'descripcion'  => trim((string) $this->request->getPost('descripcion')),
            'fecha_inicio' => $this->request->getPost('fecha_inicio') ?: date('Y-m-d'),
            'fecha_fin'    => $this->request->getPost('fecha_fin') ?: null,
            'estado'       => 1,
        ];

        if (empty($data['descripcion'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'La descripción es obligatoria.');
        }

        if (!$tratM->insert($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se pudo registrar el tratamiento.');
        }

        // Marcar la cita como completada clínicamente si la columna existe (opcional)
        if ($this->hasColumn('citas', 'estado_clinico')) {
            $citaM->update($id_cita, ['estado_clinico' => 'completada']);
        }

        return redirect()
            ->to('/veterinario/tratamientos/' . (int) $cita['id_mascota'])
            ->with('success', 'Tratamiento registrado correctamente.');
    }

    public function ver_pacientes()
    {
        $this->requireVeterinario();
        $db = \Config\Database::connect();


        $pacientes = $db->table('mascotas m')
            ->select('
                m.id_mascota,
                m.nombre,
                m.especie,
                m.raza,
                m.edad,
                m.estado,
                CONCAT(c.nombre, " ", COALESCE(c.apellido, "")) AS dueno
            ')
            ->join('clientes c', 'c.id_cliente = m.id_cliente', 'left')
            ->orderBy('m.nombre', 'ASC')
            ->get()
            ->getResult();

        return view('veterinario/pacientes', ['pacientes' => $pacientes]);
    }

}
