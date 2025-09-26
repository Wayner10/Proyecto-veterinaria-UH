<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\VeterinarioModel;
use App\Models\ClienteModel;

class Admin extends BaseController
{
    /* =======================
     * Utilidades internas
     * ======================= */

    /** Verifica si una tabla tiene una columna (para enlazar de forma segura). */
    private function hasColumn(string $table, string $column): bool
    {
        $db = \Config\Database::connect();
        return in_array($column, $db->getFieldNames($table), true);
    }

    /** Devuelve el veterinario por id_usuario si la tabla lo tiene. */
    private function getVeterinarioByUsuarioId(VeterinarioModel $vetModel, int $idUsuario): ?array
    {
        if ($this->hasColumn('veterinarios', 'id_usuario')) {
            return $vetModel->where('id_usuario', $idUsuario)->first();
        }
        return null;
    }

    /* =======================
     * Vistas básicas
     * ======================= */

    public function dashboard()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }
        return view('admin/dashboard');
    }

    public function usuarios()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $usuarioModel = new UsuarioModel();
        $usuarios = $usuarioModel->where('estado', 1)->findAll();

        return view('admin/usuarios', [
            'usuarios' => $usuarios
        ]);
    }

    public function crearUsuario()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        return view('admin/crear_usuario', [
            'validation' => \Config\Services::validation()
        ]);
    }

    /* =======================
     * Crear usuario
     * ======================= */

    public function guardarUsuario()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $usuarioModel     = new UsuarioModel();
        $veterinarioModel = new VeterinarioModel();
        $clienteModel     = new ClienteModel();

        // Primero capturamos rol para condicionar reglas
        $idRol = (string) $this->request->getPost('id_rol');

        // Reglas base
        $rules = [
            'nombre'             => 'required|min_length[3]',
            'apellido'           => 'permit_empty|max_length[100]',
            'correo_electronico' => 'required|valid_email|is_unique[usuarios.correo_electronico]',
            'contrasena'         => 'required|min_length[6]',
            'id_rol'             => 'required|in_list[1,2,3,4]',
            'estado'             => 'required|in_list[0,1]',
            'telefono'           => 'permit_empty|max_length[25]',
        ];
        // “especialidad” obligatoria SOLO si rol = Veterinario (2)
        $rules['especialidad'] = ($idRol === '2')
            ? 'required|string|min_length[3]|max_length[100]'
            : 'permit_empty|string|max_length[100]';

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        // Normalizar datos
        $nombre       = trim((string)$this->request->getPost('nombre'));
        $apellido     = trim((string)$this->request->getPost('apellido'));
        $correo       = strtolower(trim((string)$this->request->getPost('correo_electronico')));
        $clave        = (string)$this->request->getPost('contrasena');
        $estado       = (int)$this->request->getPost('estado');
        $telefono     = trim((string)$this->request->getPost('telefono'));
        $especialidad = trim((string)$this->request->getPost('especialidad'));
        $idRolInt     = (int)$idRol;

        $db = \Config\Database::connect();
        $db->transStart();

        // 1) Crear usuario
        $usuarioData = [
            'nombre'             => $nombre,
            'apellido'           => $apellido,
            'correo_electronico' => $correo,
            'contrasena'         => password_hash($clave, PASSWORD_DEFAULT),
            'id_rol'             => $idRolInt,
            'estado'             => $estado,
        ];
        $usuarioModel->insert($usuarioData);
        $idUsuario = (int)$usuarioModel->getInsertID();

        // 2) Acciones según rol
        if ($idRolInt === 2) { // Veterinario
            // Prepara datos; si existe vínculo por id_usuario, lo setea
            $vetData = [
                'nombre'       => $nombre,
                'apellido'     => $apellido,
                'telefono'     => $telefono,
                'especialidad' => $especialidad ?: 'General',
                'estado'       => 1,
            ];
            if ($this->hasColumn('veterinarios', 'id_usuario')) {
                $vetData['id_usuario'] = $idUsuario;
            }

            $okInsert = $veterinarioModel->insert($vetData);
            $idVet    = (int)$veterinarioModel->getInsertID();

            // Enlace por usuarios.id_veterinario (si existe la columna y el insert fue OK)
            if ($idVet > 0 && $this->hasColumn('usuarios', 'id_veterinario')) {
                // Usa builder para evitar problemas de allowedFields
                $db->table('usuarios')
                   ->where('id_usuario', $idUsuario)
                   ->update(['id_veterinario' => $idVet]);
            }

        } elseif ($idRolInt === 4) { // Cliente
            $cliData = [
                'nombre'   => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono,
                'estado'   => $estado,
            ];
            if ($this->hasColumn('clientes', 'id_usuario')) {
                $cliData['id_usuario'] = $idUsuario;
            }
            $clienteModel->insert($cliData);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'No se pudo guardar el usuario (o su perfil asociado).');
        }

        return redirect()->to('/admin/usuarios')->with('success', 'Usuario creado correctamente.');
    }

    /* =======================
     * Editar / Actualizar
     * ======================= */

    public function editarUsuario($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $usuarioModel     = new UsuarioModel();
        $veterinarioModel = new VeterinarioModel();

        $usuario = $usuarioModel->find($id);
        if (!$usuario) {
            return redirect()->to('/admin/usuarios')->with('error', 'Usuario no encontrado');
        }

        // Precargar veterinario (si tu esquema lo permite)
        $veterinario = null;
        if ($this->hasColumn('veterinarios', 'id_usuario')) {
            $veterinario = $veterinarioModel->where('id_usuario', $usuario['id_usuario'])->first();
        } elseif ($this->hasColumn('usuarios', 'id_veterinario') && !empty($usuario['id_veterinario'])) {
            $veterinario = $veterinarioModel->find((int)$usuario['id_veterinario']);
        }

        return view('admin/editar_usuario', [
            'usuario'      => $usuario,
            'veterinario'  => $veterinario,
            'validation'   => \Config\Services::validation()
        ]);
    }

    public function actualizarUsuario($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $usuarioModel     = new UsuarioModel();
        $veterinarioModel = new VeterinarioModel();
        $clienteModel     = new ClienteModel();

        $usuario = $usuarioModel->find($id);
        if (!$usuario) {
            return redirect()->to('/admin/usuarios')->with('error', 'Usuario no encontrado');
        }

        $idRolNuevo = (string)$this->request->getPost('id_rol');

        // Reglas base
        $rules = [
            'nombre'             => 'required|min_length[3]',
            'apellido'           => 'permit_empty|max_length[100]',
            'correo_electronico' => 'required|valid_email',
            'id_rol'             => 'required|in_list[1,2,3,4]',
            'estado'             => 'required|in_list[0,1]',
            'telefono'           => 'permit_empty|max_length[25]',
            'contrasena'         => 'permit_empty|min_length[6]',
        ];
        // “especialidad” obligatoria si rol = 2 (veterinario)
        $rules['especialidad'] = ($idRolNuevo === '2')
            ? 'required|string|min_length[3]|max_length[100]'
            : 'permit_empty|string|max_length[100]';

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        // Datos normalizados
        $nombre       = trim((string)$this->request->getPost('nombre'));
        $apellido     = trim((string)$this->request->getPost('apellido'));
        $correo       = strtolower(trim((string)$this->request->getPost('correo_electronico')));
        $estado       = (int)$this->request->getPost('estado');
        $telefono     = trim((string)$this->request->getPost('telefono'));
        $especialidad = trim((string)$this->request->getPost('especialidad'));
        $idRolInt     = (int)$idRolNuevo;
        $password     = (string)$this->request->getPost('contrasena');

        $db = \Config\Database::connect();
        $db->transStart();

        // 1) Actualizar usuario
        $dataUser = [
            'nombre'             => $nombre,
            'apellido'           => $apellido,
            'correo_electronico' => $correo,
            'id_rol'             => $idRolInt,
            'estado'             => $estado,
        ];
        if (!empty($password)) {
            $dataUser['contrasena'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $usuarioModel->update($id, $dataUser);

        // 2) Sincronizar perfil asociado según rol

        // --- Veterinario ---
        if ($idRolInt === 2) {
            if ($this->hasColumn('veterinarios', 'id_usuario')) {
                // Upsert por id_usuario
                $existing = $this->getVeterinarioByUsuarioId($veterinarioModel, $id);
                $vetData = [
                    'nombre'       => $nombre,
                    'apellido'     => $apellido,
                    'telefono'     => $telefono,
                    'especialidad' => $especialidad ?: 'General',
                    'estado'       => 1,
                    'id_usuario'   => $id,
                ];
                if ($existing) {
                    $veterinarioModel->where('id_usuario', $id)->set($vetData)->update();
                } else {
                    $veterinarioModel->insert($vetData);
                }
            } else {
                // Enlace por usuarios.id_veterinario
                $idVetEnUsuario = null;
                if ($this->hasColumn('usuarios', 'id_veterinario')) {
                    $userReload = $usuarioModel->find($id);
                    $idVetEnUsuario = $userReload['id_veterinario'] ?? null;
                }

                $vetData = [
                    'nombre'       => $nombre,
                    'apellido'     => $apellido,
                    'telefono'     => $telefono,
                    'especialidad' => $especialidad ?: 'General',
                    'estado'       => 1,
                ];

                if ($idVetEnUsuario) {
                    $veterinarioModel->update($idVetEnUsuario, $vetData);
                } else {
                    $veterinarioModel->insert($vetData);
                    $nuevoIdVet = (int)$veterinarioModel->getInsertID();
                    if ($nuevoIdVet > 0 && $this->hasColumn('usuarios', 'id_veterinario')) {
                        // Usa builder para evitar problemas de allowedFields
                        $db->table('usuarios')
                           ->where('id_usuario', $id)
                           ->update(['id_veterinario' => $nuevoIdVet]);
                    }
                }
            }
        } else {
            // Si NO es veterinario, limpia el vínculo según el esquema
            if ($this->hasColumn('usuarios', 'id_veterinario')) {
                $db->table('usuarios')
                   ->where('id_usuario', $id)
                   ->update(['id_veterinario' => null]);
            }
            if ($this->hasColumn('veterinarios', 'id_usuario')) {
                // Desactiva perfil de veterinario (opcional)
                $veterinarioModel->where('id_usuario', $id)->set(['estado' => 0])->update();
            }
        }

        // --- Cliente (opcional similar al alta) ---
        if ($idRolInt === 4) {
            $cliData = [
                'nombre'   => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono,
                'estado'   => $estado,
            ];
            if ($this->hasColumn('clientes', 'id_usuario')) {
                $cliData['id_usuario'] = $id;
                $exists = $this->hasColumn('clientes', 'id_usuario')
                    ? (new ClienteModel())->where('id_usuario', $id)->first()
                    : null;

                if ($exists) {
                    $clienteModel->where('id_usuario', $id)->set($cliData)->update();
                } else {
                    $clienteModel->insert($cliData);
                }
            } else {
                $clienteModel->insert($cliData);
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'No se pudo actualizar el usuario (o su perfil asociado).');
        }

        return redirect()->to('/admin/usuarios')->with('success', 'Usuario actualizado correctamente');
    }

    /* =======================
     * Eliminar / Reactivar
     * ======================= */

    public function eliminar($id = null)
    {
        $usuarioModel = new UsuarioModel();

        if (!$id || !$usuarioModel->find($id)) {
            return redirect()->back()->with('error', 'Usuario no encontrado');
        }

        $usuarioModel->update($id, ['estado' => 0]);

        return redirect()->to('/admin/usuarios')->with('success', 'Usuario desactivado correctamente');
    }

    public function usuariosEliminados()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $usuarioModel = new UsuarioModel();
        $usuarios = $usuarioModel->where('estado', 0)->findAll();

        return view('admin/usuarios_eliminados', [
            'usuarios' => $usuarios
        ]);
    }

    public function reactivar($id = null)
    {
        $usuarioModel = new UsuarioModel();

        if (!$id || !$usuarioModel->find($id)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'error' => 'Usuario no encontrado']);
            }
            return redirect()->back()->with('error', 'Usuario no encontrado');
        }

        $usuarioModel->update($id, ['estado' => 1]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->to('/admin/usuarios/eliminados')->with('success', 'Usuario reactivado correctamente');
    }

    /* =======================
     * Configuración / Reportes
     * ======================= */

    public function configuracion()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        return view('admin/configuracion');
    }

    public function reportes()
    {
        $usuarioModel = new UsuarioModel();

        $rol    = $this->request->getGet('rol');
        $estado = $this->request->getGet('estado');

        $builder = $usuarioModel->builder();

        if (!empty($rol)) {
            $builder->where('id_rol', $rol);
        }
        if ($estado !== null && $estado !== '') {
            $builder->where('estado', $estado);
        }

        $usuarios = $builder->get()->getResultArray();

        $rolesCount  = [];
        $estadoCount = ['activos' => 0, 'inactivos' => 0];

        foreach ($usuarios as $u) {
            $rolesCount[$u['id_rol']] = ($rolesCount[$u['id_rol']] ?? 0) + 1;
            if (!empty($u['estado'])) {
                $estadoCount['activos']++;
            } else {
                $estadoCount['inactivos']++;
            }
        }

        return view('admin/reportes', [
            'usuarios'    => $usuarios,
            'rolesCount'  => $rolesCount,
            'estadoCount' => [$estadoCount['activos'], $estadoCount['inactivos']],
        ]);
    }
}
