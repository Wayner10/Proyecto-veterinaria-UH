<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\VeterinarioModel; // 👈 añadidos
use App\Models\ClienteModel;     // (opcional, por si luego quieres setear id_cliente)

class Auth extends BaseController
{
    public function login()
    {
        helper(['form']);

        if ($this->request->is('post')) {
            $correo   = strtolower(trim((string)$this->request->getPost('correo_electronico')));
            $password = (string)$this->request->getPost('contrasena');

            log_message('debug', 'Intento de login con correo: ' . $correo);

            $usuarioModel     = new UsuarioModel();
            $veterinarioModel = new VeterinarioModel();

            $usuario = $usuarioModel->where('correo_electronico', $correo)->first();

            if (!$usuario) {
                log_message('error', 'Correo no encontrado: ' . $correo);
                return redirect()->back()->with('error', 'Correo no encontrado');
            }

            if (!isset($usuario['contrasena']) || !password_verify($password, $usuario['contrasena'])) {
                log_message('error', 'Contraseña incorrecta para correo: ' . $correo);
                return redirect()->back()->with('error', 'Contraseña incorrecta');
            }

            if (empty($usuario['estado'])) {
                log_message('error', 'Usuario inactivo: ' . $correo);
                return redirect()->back()->with('error', 'Usuario inactivo');
            }

            log_message('debug', 'Usuario autenticado correctamente: ' . print_r($usuario, true));

            // ==============================
            // Resolver id_veterinario (si rol = 2)
            // ==============================
            $idVeterinario = null;

            if ((int)$usuario['id_rol'] === 2) {
                $db = \Config\Database::connect();
                $fieldsVets  = $db->getFieldNames('veterinarios');
                $fieldsUsers = $db->getFieldNames('usuarios');

                // Opción A: veterinarios.id_usuario
                if (in_array('id_usuario', $fieldsVets, true)) {
                    $vet = $veterinarioModel->select('id_veterinario')
                        ->where('id_usuario', (int)$usuario['id_usuario'])
                        ->first();
                    if ($vet) {
                        $idVeterinario = (int)$vet['id_veterinario'];
                        log_message('debug', 'Vet por id_usuario -> id_veterinario=' . $idVeterinario);
                    }
                }

                // Opción B: usuarios.id_veterinario
                if ($idVeterinario === null && in_array('id_veterinario', $fieldsUsers, true)) {
                    if (!empty($usuario['id_veterinario'])) {
                        $idVeterinario = (int)$usuario['id_veterinario'];
                        log_message('debug', 'Vet por usuarios.id_veterinario -> ' . $idVeterinario);
                    }
                }

                // Fallback: buscar por nombre (SOLO si hay coincidencia única)
                if ($idVeterinario === null) {
                    $cands = $veterinarioModel->select('id_veterinario')
                        ->where('nombre', $usuario['nombre'])
                        ->findAll(2); // como mucho 2 para saber si es único
                    if (count($cands) === 1) {
                        $idVeterinario = (int)$cands[0]['id_veterinario'];
                        log_message('debug', 'Vet por nombre único -> ' . $idVeterinario);
                    } else {
                        log_message('warning', 'No se pudo resolver id_veterinario (sin vínculo o nombre no único).');
                    }
                }

                // Si sigues sin resolver, muestra error claro
                if ($idVeterinario === null) {
                    return redirect()->back()->with('error',
                        'No se encontró tu perfil de veterinario. ' .
                        'Asegúrate de tener el vínculo (veterinarios.id_usuario o usuarios.id_veterinario).'
                    );
                }
            }

            // ==============================
            // Armar sesión
            // ==============================
            $sessionData = [
                'logged_in'      => true,
                'id_usuario'     => (int)$usuario['id_usuario'],
                'id_rol'         => (int)$usuario['id_rol'],
                'nombre'         => $usuario['nombre'] ?? '',
                'correo'         => $usuario['correo_electronico'],
                'id_veterinario' => ((int)$usuario['id_rol'] === 2 ? $idVeterinario : null),
            ];
            session()->set($sessionData);

            log_message('debug', 'Rol del usuario: ' . $usuario['id_rol'] . ' | id_veterinario=' . var_export($idVeterinario, true));

            // Redirigir según el rol
            switch ((int) $usuario['id_rol']) {
                case 1:
                    log_message('debug', 'Redirigiendo a /admin/dashboard');
                    return redirect()->to('/admin/dashboard');
                case 2:
                    log_message('debug', 'Redirigiendo a /veterinario/inicio');
                    return redirect()->to('/veterinario/inicio');
                case 3:
                    log_message('debug', 'Redirigiendo a /recepcion/inicio');
                    return redirect()->to('/recepcion/inicio');
                case 4:
                    log_message('debug', 'Redirigiendo a /cliente/inicio');
                    return redirect()->to('/cliente/inicio');
                default:
                    log_message('error', 'Rol no válido para el usuario: ' . $correo);
                    return redirect()->to('/login')->with('error', 'Rol no válido');
            }
        }

        return view('auth/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'Sesión cerrada correctamente.');
    }
}
