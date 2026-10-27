<?php
namespace App\Core;

class Csrf
{
    /**
     * Devuelve el token de la sesión. Si no existe, lo crea.
     * Es un único token por sesión.
     */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * HTML del campo oculto que va dentro de cada formulario POST.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::token()) . '">';
    }

    /**
     * Comprueba el token recibido por POST contra el de la sesión.
     */
    public static function isValid(): bool
    {
        $sent  = $_POST['csrf_token'] ?? '';
        $token = $_SESSION['csrf_token'] ?? '';

        return is_string($sent)
            && $token !== ''
            && hash_equals($token, $sent);
    }
}