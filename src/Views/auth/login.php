<h1>Iniciar sesión</h1>
<form method="post" action="/index.php?controller=Auth&action=login">
    <?= \App\Core\Csrf::field() ?>
    <label>Email
        <input type="email" name="email" required>
    </label>
    <label>Contraseña
        <input type="password" name="password" required>
    </label>
    <button type="submit">Entrar</button>
</form>