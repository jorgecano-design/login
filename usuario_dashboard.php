<?php
session_start();
// Solo pueden entrar usuarios con rol 'usuario' (o admin también podría, pero aquí lo limitamos a usuario)
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'usuario') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Usuario</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 40px auto; background: #f0f0f0; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .btn-salir { background: #a00; color: white; padding: 6px 12px; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
<div class="card">
    <h1>📘 Panel de Usuario</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?> (rol: usuario)</p>
    <p>Aquí puedes ver tu perfil, tus compras, etc.</p>
    <a href="login.php?salir=1" class="btn-salir">Cerrar sesión</a>
</div>
<div class="card">
    <h3>Contenido para usuarios</h3>
    <ul>
        <li>Ver catálogo de productos</li>
        <li>Agregar al carrito</li>
        <li>Ver historial de pedidos</li>
    </ul>
</div>
</body>
</html>