<?php
/** @var array $posts */
/** @var string $search */
?>
<h1>Anuncios</h1>

<form method="get" action="/index.php">
    <input type="hidden" name="controller" value="Post">
    <input type="hidden" name="action" value="index">
    <input type="text" name="q" placeholder="Buscar por título o categoría"
           value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Buscar</button>
</form>

<?php if (isset($_SESSION['user_id'])): ?>
    <p><a href="/index.php?controller=Post&action=create">+ Nuevo anuncio</a></p>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <p>No hay anuncios<?= $search !== '' ? ' que coincidan con tu búsqueda' : '' ?>.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article style="border:1px solid #ddd; padding:.75rem; margin:.75rem 0;">
            <h2 style="margin:0;">
                <a href="/index.php?controller=Post&action=show&id=<?= (int) $post['id'] ?>">
                    <?= htmlspecialchars($post['title']) ?>
                </a>
            </h2>
            <small>
                <?= htmlspecialchars($post['category']) ?> ·
                por <?= htmlspecialchars($post['username']) ?> ·
                <?= htmlspecialchars($post['created_at']) ?>
            </small>
        </article>
    <?php endforeach; ?>
<?php endif; ?>