<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User(new Database());
    }

    public function showRegisterForm(): void
    {
        $this->render('auth/register');
    }

    public function register(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $username === '' || strlen($password) < 6) {
            $this->render('auth/register', [
                'error' => 'Rellena todos los campos. La contraseña debe tener al menos 6 caracteres.',
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/register', ['error' => 'El email no es válido.']);
            return;
        }

        try {
            $this->users->insert([
                'email'    => $email,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (\PDOException $e) {
            // Código 23000 = violación de restricción (email UNIQUE duplicado)
            if ($e->getCode() === '23000') {
                $this->render('auth/register', ['error' => 'Ese email ya está registrado.']);
                return;
            }
            throw $e;
        }

        $this->setFlash('Registro completado. Ya puedes iniciar sesión.');
        $this->redirect('/index.php?controller=Auth&action=showLoginForm');
    }

    public function showLoginForm(): void
    {
        $this->render('auth/login');
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $id = $this->users->findByEmail($email, $password);

        if ($id === null) {
            $this->render('auth/login', ['error' => 'Email o contraseña incorrectos.']);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = $id;
        $_SESSION['user_email'] = $email;

        $this->redirect('/index.php');
    }

    public function logout(): void
    {
        session_destroy();
        session_start();
        $this->setFlash('Has cerrado sesión.');
        $this->redirect('/index.php');
    }
}