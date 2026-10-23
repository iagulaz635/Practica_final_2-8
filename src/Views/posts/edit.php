<h1>Editar anuncio</h1>
<form method="post" action="/index.php?controller=Post&action=update">
    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
    <label>Título
        <input type="text" name="title" required maxlength="200"
               value="<?= htmlspecialchars($post['title']) ?>">
    </label>
    <label>Categoría
        <select name="category" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"
                    <?= $post['category'] === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Contenido
        <textarea name="body" rows="6" required><?= htmlspecialchars($post['body']) ?></textarea>
    </label>
    <button type="submit">Guardar cambios</button>
</form>
<p><a href="/index.php?controller=Post&action=show&id=<?= (int) $post['id'] ?>">Cancelar</a></p>