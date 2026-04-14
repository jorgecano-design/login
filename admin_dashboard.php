<?php
// Iniciamos sesión para verificar quién entra
session_start();

// Si no hay sesión o no es admin, lo mandamos al login
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 40px auto; background: #f8f9fa; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .btn-salir { background: #a00; color: white; padding: 6px 12px; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
<div class="card">
    <h1>👑 Panel de Administrador</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?> (rol: admin)</p>
    <p>Aquí puedes gestionar usuarios, productos, etc.</p>
    <a href="index.php?salir=1" class="btn-salir">Cerrar sesión</a>
</div>
<div class="card">
    <h3>Acciones de admin</h3>
    <ul>
        <li>Crear nuevos usuarios</li>
        <li>Ver reportes</li>
        <li>Configurar el sistema</li>
    </ul>
</div>
</body>
</html>