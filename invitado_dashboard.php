<?php
session_start();
// Solo pueden entrar invitados (o cualquiera que no sea admin/usuario, pero aquí lo dejamos claro)
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'invitado') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Zona de Invitado</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 40px auto; background: #e9ecef; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .btn-salir { background: #a00; color: white; padding: 6px 12px; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
<div class="card">
    <h1>👀 Bienvenido, Invitado</h1>
    <p>Hola <?php echo htmlspecialchars($_SESSION['usuario']); ?> (rol: invitado)</p>
    <p>Aquí puedes ver información pública, pero no tienes todos los privilegios.</p>
    <a href="login.php?salir=1" class="btn-salir">Cerrar sesión</a>
</div>
<div class="card">
    <h3>Contenido para invitados</h3>
    <ul>
        <li>Ver productos (sin precios especiales)</li>
        <li>Registrarse para ser usuario normal</li>
    </ul>
</div>
</body>
</html>