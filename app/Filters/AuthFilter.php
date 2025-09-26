<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $isLogged = (bool) $session->get('logged_in');
        $idUsuario = (int) $session->get('id_usuario');
        $idRol = (int) $session->get('id_rol');

        if (!$isLogged || !$idUsuario || !$idRol) {

            // Si es AJAX o espera JSON -> 401
            $accept = (string) $request->getHeaderLine('Accept');
            if ($request->isAJAX() || stripos($accept, 'application/json') !== false) {
                return service('response')->setStatusCode(401)
                    ->setJSON(['message' => 'No autenticado']);
            }

            // Guardar URL destino para volver tras login
            $session->set('intended_url', current_url(true)->__toString());

            // Redirigir a la ruta correcta
            return redirect()->to(base_url('auth/login'))
                             ->with('error', 'Debes iniciar sesión.');
        }

        // Blindaje extra: si se detecta cambio de rol en sesión, restaurarlo
        $lock = (int) $session->get('role_lock');
        if ($lock && $idRol !== $lock) {
            log_message('warning', 'Cambio de rol detectado; se restaura role_lock.');
            $session->set('id_rol', $lock);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Evitar cache de páginas protegidas
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
