<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Post;
use App\Models\Comment;

class PostController extends Controller
{
    private Post $posts;
    private Comment $comments;

    public const CATEGORIES = ['Venta', 'Búsqueda', 'Objetos perdidos', 'Proyectos', 'Otros'];

    public function __construct()
    {
        $db             = new Database();
        $this->posts    = new Post($db);
        $this->comments = new Comment($db);
    }

    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $posts  = $this->posts->search($search);

        $this->render('posts/index', [
            'posts'  => $posts ?? [],
            'search' => $search,
        ]);
    }

    public function show(): void
    {
        $id   = (int) ($_GET['id'] ?? 0);
        $post = $this->posts->findWithAuthor($id);

        if ($post === null) {
            $this->abort(404, 'La publicación no existe.');
        }

        $this->render('posts/show', [
            'post'     => $post,
            'comments' => $this->comments->getByPost($id),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->render('posts/create', ['categories' => self::CATEGORIES]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $this->requireLogin();

        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $category = $_POST['category'] ?? '';

        if ($error = $this->validate($title, $body, $category)) {
            $this->render('posts/create', [
                'categories' => self::CATEGORIES,
                'error'      => $error,
                'old'        => compact('title', 'body', 'category'),
            ]);
            return;
        }

        $id = $this->posts->insert([
            'user_id'  => $_SESSION['user_id'],
            'title'    => $title,
            'body'     => $body,
            'category' => $category,
        ]);

        $this->setFlash('Publicación creada.');
        $this->redirect("/index.php?controller=Post&action=show&id=$id");
    }

    public function edit(): void
    {
        $this->requireLogin();
        $post = $this->findOwnedPost();

        $this->render('posts/edit', [
            'post'       => $post,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(): void
    {
        $this->verifyCsrf();
        $this->requireLogin();
        $post = $this->findOwnedPost();

        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $category = $_POST['category'] ?? '';

        if ($error = $this->validate($title, $body, $category)) {
            $post = array_merge($post, compact('title', 'body', 'category'));
            $this->render('posts/edit', [
                'post'       => $post,
                'categories' => self::CATEGORIES,
                'error'      => $error,
            ]);
            return;
        }

        $this->posts->update($post['id'], [
            'title'    => $title,
            'body'     => $body,
            'category' => $category,
        ]);

        $this->setFlash('Publicación actualizada.');
        $this->redirect("/index.php?controller=Post&action=show&id={$post['id']}");
    }

    public function destroy(): void
    {
        $this->verifyCsrf();
        $this->requireLogin();
        $post = $this->findOwnedPost();

        $this->posts->delete($post['id']);

        $this->setFlash('Publicación eliminada.');
        $this->redirect('/index.php?controller=Post&action=index');
    }

    /**
     * Si no hay sesión, redirige al login con un mensaje.
     */
    private function requireLogin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('Debes iniciar sesión para hacer eso.');
            $this->redirect('/index.php?controller=Auth&action=showLoginForm');
        }
    }

    /**
     * Autorización: devuelve el post solo si existe y es del usuario logueado.
     * El id viene de GET (en edit) o de POST (en update/destroy).
     */
    private function findOwnedPost(): array
    {
        $id   = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $post = $this->posts->getById($id);

        if ($post === null) {
            $this->abort(404, 'La publicación no existe.');
        }

        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            $this->abort(403, 'No tienes permiso para hacer esto.');
        }

        return $post;
    }

    private function validate(string $title, string $body, string $category): ?string
    {
        if ($title === '' || $body === '') {
            return 'El título y el contenido son obligatorios.';
        }
        if (mb_strlen($title) > 200) {
            return 'El título no puede superar los 200 caracteres.';
        }
        if (!in_array($category, self::CATEGORIES, true)) {
            return 'Selecciona una categoría válida.';
        }
        return null;
    }
}