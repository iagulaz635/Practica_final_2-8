<?php
/** @var string[] $categories */
/** @var array|null $old */
?>
<h1>Nuevo anuncio</h1>
<form method="post" action="/index.php?controller=Post&action=store">
    <?= \App\Core\Csrf::field() ?>
    <label>Título
        <input type="text" name="title" required maxlength="200"
               value="<?= htmlspecialchars($old['title'] ?? '') ?>">
    </label>
    <label>Categoría
        <select name="category" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"
                    <?= ($old['category'] ?? '') === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Contenido
        <textarea name="body" rows="6" required><?= htmlspecialchars($old['body'] ?? '') ?></textarea>
    </label>
    <button type="submit">Publicar</button>
</form>