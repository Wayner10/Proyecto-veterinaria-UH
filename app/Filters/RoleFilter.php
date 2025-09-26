<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = (int) session()->get('id_rol');
        $lock = (int) session()->get('role_lock'); // rol original del login

        // Sin sesión -> login
        if (!$role || !session()->get('logged_in')) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Sesión expirada.');
        }

        // Si por error alguien cambió el rol en sesión, restablécelo o cierra sesión.
        if ($lock && $role !== $lock) {
            log_message('warning', 'Intento de cambio de rol detectado. Se restablece.');
            session()->set('id_rol', $lock);
            $role = $lock;
        }

        // Autorización
        if ($arguments && !in_array($role, array_map('intval', $arguments), true)) {
            return redirect()
                ->to(base_url('/'))
                ->with('error', 'No tienes permisos para acceder.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {}
}
