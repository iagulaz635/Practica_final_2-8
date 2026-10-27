<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tablón de anuncios</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 0 auto; padding: 1rem; }
        nav { display: flex; justify-content: space-between; border-bottom: 1px solid #ccc; padding-bottom: .5rem; margin-bottom: 1rem; }
        .flash { background: #e0f2e0; padding: .5rem 1rem; margin-bottom: 1rem; }
        .error { background: #fbe0e0; padding: .5rem 1rem; margin-bottom: 1rem; }
        label { display: block; margin-top: .5rem; }
        input, textarea { width: 100%; padding: .4rem; box-sizing: border-box; }
        button { margin-top: 1rem; padding: .5rem 1rem; }
        .inline-form { display: inline; }
        .inline-form button { margin: 0; padding: 0; border: 0; background: none; color: #0645ad; text-decoration: underline; cursor: pointer; font: inherit; }
    </style>
</head>
<body>
<nav>
    <a href="/index.php">Tablón de anuncios</a>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?= htmlspecialchars($_SESSION['user_email']) ?> |
            <form class="inline-form" method="post" action="/index.php?controller=Auth&action=logout">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit">Cerrar sesión</button>
            </form>
        <?php else: ?>
            <a href="/index.php?controller=Auth&action=showLoginForm">Iniciar sesión</a> |
            <a href="/index.php?controller=Auth&action=showRegisterForm">Registrarse</a>
        <?php endif; ?>
    </div>
</nav>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?></div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>