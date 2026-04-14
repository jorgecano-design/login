<?php
// Iniciamos sesión para guardar datos del usuario
session_start();

// Archivo donde guardamos los usuarios
$archivo_usuarios = 'usuarios.json';

// Si no existe el archivo, creamos usuarios de ejemplo con distintos roles
if (!file_exists($archivo_usuarios)) {
    // Encriptamos las contraseñas
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $usuario_pass = password_hash('usuario123', PASSWORD_DEFAULT);
    $invitado_pass = password_hash('invitado123', PASSWORD_DEFAULT);

    $usuarios_iniciales = [
        'admin' => [
            'password' => $admin_pass,
            'rol' => 'admin'
        ],
        'carlos' => [
            'password' => $usuario_pass,
            'rol' => 'usuario'
        ],
        'invitado' => [
            'password' => $invitado_pass,
            'rol' => 'invitado'
        ]
    ];
    file_put_contents($archivo_usuarios, json_encode($usuarios_iniciales));
}

// Leemos los usuarios actuales
$usuarios = json_decode(file_get_contents($archivo_usuarios), true);

// Cerrar sesión (si viene por GET)
if (isset($_GET['salir'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// ===================================================
// PROCESAR LOGIN (cuando envían el formulario)
// ===================================================
$error_login = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $nombre = $_POST['nombre'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if (isset($usuarios[$nombre]) && password_verify($pass, $usuarios[$nombre]['password'])) {
        // Guardamos datos en sesión
        $_SESSION['usuario'] = $nombre;
        $_SESSION['rol'] = $usuarios[$nombre]['rol'];

        // Redirigir según el rol
        if ($_SESSION['rol'] == 'admin') {
            header('Location: admin_dashboard.php');
        } elseif ($_SESSION['rol'] == 'usuario') {
            header('Location: usuario_dashboard.php');
        } else {
            header('Location: invitado_dashboard.php');
        }
        exit;
    } else {
        $error_login = 'Usuario o contraseña incorrectos';
    }
}

// ===================================================
// PROCESAR CREACIÓN DE NUEVOS USUARIOS (solo admin)
// ===================================================
$mensaje_creacion = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_usuario'])) {
    // Verificamos que quien envía el formulario sea admin y esté logueado
    if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') {
        $nuevo_nombre = trim($_POST['nuevo_nombre'] ?? '');
        $nueva_pass = $_POST['nueva_pass'] ?? '';
        $nuevo_rol = $_POST['nuevo_rol'] ?? 'usuario';

        if ($nuevo_nombre !== '' && $nueva_pass !== '') {
            if (!isset($usuarios[$nuevo_nombre])) {
                $usuarios[$nuevo_nombre] = [
                    'password' => password_hash($nueva_pass, PASSWORD_DEFAULT),
                    'rol' => $nuevo_rol
                ];
                file_put_contents($archivo_usuarios, json_encode($usuarios));
                $mensaje_creacion = "✅ Usuario $nuevo_nombre creado con rol $nuevo_rol.";
            } else {
                $mensaje_creacion = "⚠️ Ese nombre de usuario ya existe.";
            }
        } else {
            $mensaje_creacion = "⚠️ Completa todos los campos.";
        }
    } else {
        $mensaje_creacion = "⛔ No tienes permisos para crear usuarios.";
    }
}

// Preparamos el listado de usuarios (solo visible para admin)
$listado_usuarios = '';
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') {
    $listado_usuarios = '<h3>📋 Usuarios registrados</h3><ul>';
    foreach ($usuarios as $nombre => $datos) {
        $listado_usuarios .= "<li><strong>$nombre</strong> - Rol: {$datos['rol']}</li>";
    }
    $listado_usuarios .= '</ul>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login con roles + admin puede crear usuarios</title>
    <style>
        body { font-family: Arial; max-width: 700px; margin: 40px auto; background: #f0f0f0; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select, button { width: 100%; padding: 8px; margin: 6px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #2c7da0; color: white; border: none; cursor: pointer; }
        .error { background: #ffe6e6; color: #c00; padding: 8px; border-radius: 6px; }
        .exito { background: #e0ffe0; color: #080; padding: 8px; border-radius: 6px; }
        hr { margin: 15px 0; }
    </style>
</head>
<body>

<?php if (isset($_SESSION['usuario'])): ?>
    <!-- ==================== USUARIO YA LOGUEADO ==================== -->
    <div class="card">
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></h2>
        <p>Tu rol es: <strong><?php echo $_SESSION['rol']; ?></strong></p>
        <p>✅ Has iniciado sesión correctamente.</p>
        <a href="?salir=1" style="display: inline-block; background: #a00; color: white; padding: 6px 12px; text-decoration: none; border-radius: 6px;">Cerrar sesión</a>
    </div>

    <!-- Panel de administración (solo visible para admin) -->
    <?php if ($_SESSION['rol'] == 'admin'): ?>
        <div class="card">
            <h3>➕ Crear nuevo usuario</h3>
            <?php if ($mensaje_creacion): ?>
                <div class="<?php echo strpos($mensaje_creacion, '✅') !== false ? 'exito' : 'error'; ?>">
                    <?php echo $mensaje_creacion; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <label>Nombre de usuario:</label>
                <input type="text" name="nuevo_nombre" required>
                <label>Contraseña:</label>
                <input type="password" name="nueva_pass" required>
                <label>Rol:</label>
                <select name="nuevo_rol">
                    <option value="admin">admin</option>
                    <option value="usuario">usuario</option>
                    <option value="invitado">invitado</option>
                </select>
                <button type="submit" name="crear_usuario">Crear usuario</button>
            </form>
            <?php echo $listado_usuarios; ?>
        </div>
    <?php endif; ?>

    <!-- Información adicional para todos los roles -->
    <div class="card">
        <h3>🔐 Acceso por rol</h3>
        <?php if ($_SESSION['rol'] == 'admin'): ?>
            <p>👑 Tienes acceso total: puedes crear usuarios, ver listados, y administrar.</p>
        <?php elseif ($_SESSION['rol'] == 'usuario'): ?>
            <p>📖 Puedes ver contenido normal y hacer compras (en la futura tienda).</p>
        <?php else: ?>
            <p>👀 Solo puedes ver información básica. Para más, inicia sesión con un rol superior.</p>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ==================== FORMULARIO DE LOGIN ==================== -->
    <div class="card">
        <h2>Iniciar sesión</h2>
        <p><strong>Usuarios de ejemplo:</strong></p>
        <ul>
            <li>admin / admin123 (rol admin)</li>
            <li>carlos / usuario123 (rol usuario)</li>
            <li>invitado / invitado123 (rol invitado)</li>
        </ul>
        <?php if ($error_login): ?>
            <div class="error"><?php echo $error_login; ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Usuario:</label>
            <input type="text" name="nombre" required>
            <label>Contraseña:</label>
            <input type="password" name="pass" required>
            <button type="submit" name="login">Entrar</button>
        </form>
        <hr>
        <p style="font-size: 0.8em;">El administrador puede crear más usuarios después de entrar.</p>
    </div>
<?php endif; ?>

</body>
</html>