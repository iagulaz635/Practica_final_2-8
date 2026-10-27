<?php
namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . "/../Views/{$view}.php";
        require __DIR__ . '/../Views/layout/footer.php';
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    protected function setFlash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }

    protected function abort(int $code, string $message): void
    {
        http_response_code($code);
        $this->render('error', ['code' => $code, 'message' => $message]);
        exit;
    }

    /**
     * Se llama al principio de toda acción que reciba un POST.
     * Si el token no coincide, corta la ejecución con un 403.
     */
    protected function verifyCsrf(): void
    {
        if (!Csrf::isValid()) {
            $this->abort(403, 'Token de seguridad no válido. Recarga la página e inténtalo de nuevo.');
        }
    }
}