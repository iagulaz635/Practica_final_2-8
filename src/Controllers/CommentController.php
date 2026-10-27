<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    private Comment $comments;
    private Post $posts;

    public function __construct()
    {
        $db             = new Database();
        $this->comments = new Comment($db);
        $this->posts    = new Post($db);
    }

    public function store(): void
    {
        $this->verifyCsrf();

        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('Debes iniciar sesión para comentar.');
            $this->redirect('/index.php?controller=Auth&action=showLoginForm');
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        $body   = trim($_POST['body'] ?? '');

        if ($this->posts->getById($postId) === null) {
            $this->abort(404, 'La publicación no existe.');
        }

        $postUrl = "/index.php?controller=Post&action=show&id=$postId";

        if ($body === '' || mb_strlen($body) > 1000) {
            $this->setFlash('El comentario no puede estar vacío ni superar los 1000 caracteres.');
            $this->redirect($postUrl);
        }

        $this->comments->insert([
            'post_id' => $postId,
            'user_id' => $_SESSION['user_id'],
            'body'    => $body,
        ]);

        $this->setFlash('Comentario publicado.');
        $this->redirect($postUrl);
    }
}