<h1><?= htmlspecialchars($post['title']) ?></h1>
<p>
    <small>
        <?= htmlspecialchars($post['category']) ?> ·
        por <?= htmlspecialchars($post['username']) ?> ·
        <?= htmlspecialchars($post['created_at']) ?>
    </small>
</p>

<p><?= nl2br(htmlspecialchars($post['body'])) ?></p>

<?php if (isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $post['user_id']): ?>
    <p>
        <a href="/index.php?controller=Post&action=edit&id=<?= (int) $post['id'] ?>">Editar</a>
    </p>
    <form method="post" action="/index.php?controller=Post&action=destroy"
          onsubmit="return confirm('¿Seguro que quieres borrar este anuncio?');">
        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
        <button type="submit">Borrar</button>
    </form>
<?php endif; ?>

<p><a href="/index.php?controller=Post&action=index">← Volver al listado</a></p>