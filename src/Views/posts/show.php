<?php

/** @var array $post */
/** @var array $comments */
?>
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
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
        <button type="submit">Borrar</button>
    </form>
<?php endif; ?>

<hr>
<h2>Comentarios (<?= count($comments) ?>)</h2>

<?php if (empty($comments)): ?>
    <p>Todavía no hay comentarios.</p>
<?php else: ?>
    <?php foreach ($comments as $comment): ?>
        <div style="border-left:3px solid #ccc; padding-left:.75rem; margin:.75rem 0;">
            <small>
                <strong><?= htmlspecialchars($comment['username']) ?></strong> ·
                <?= htmlspecialchars($comment['created_at']) ?>
            </small>
            <p style="margin:.25rem 0;"><?= nl2br(htmlspecialchars($comment['body'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <form method="post" action="/index.php?controller=Comment&action=store">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
        <label>Añadir comentario
            <textarea name="body" rows="3" required maxlength="1000"></textarea>
        </label>
        <button type="submit">Comentar</button>
    </form>
<?php else: ?>
    <p><a href="/index.php?controller=Auth&action=showLoginForm">Inicia sesión</a> para comentar.</p>
<?php endif; ?>

<p><a href="/index.php?controller=Post&action=index">← Volver al listado</a></p>