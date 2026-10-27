<h1>Registro</h1>
<form method="post" action="/index.php?controller=Auth&action=register">
    <?= \App\Core\Csrf::field() ?>
    <label>Email
        <input type="email" name="email" required>
    </label>
    <label>Nombre de usuario
        <input type="text" name="username" required>
    </label>
    <label>Contraseña (mínimo 6 caracteres)
        <input type="password" name="password" required minlength="6">
    </label>
    <button type="submit">Registrarme</button>
</form>