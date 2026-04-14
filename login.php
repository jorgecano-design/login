<?php
// Iniciamos la sesión para recordar al usuario
session_start();

// Definimos la ruta del archivo donde guardaremos los usuarios
$archivo_usuarios = 'usuarios.json';

// ------------------------------------------------------------
// FUNCIÓN: cargar usuarios desde el archivo JSON
// Si el archivo no existe o está dañado, devuelve un array vacío
// ------------------------------------------------------------
function cargarUsuarios($archivo) {
    if (!file_exists($archivo)) {
        return [];  // no existe, empezamos vacío
    }
    $contenido = file_get_contents($archivo);
    $datos = json_decode($contenido, true);
    if (is_array($datos)) {
        return $datos;
    }
    return []; // si el json no es válido, array vacío
}

// ------------------------------------------------------------
// FUNCIÓN: guardar usuarios en el archivo JSON
// ------------------------------------------------------------
function guardarUsuarios($archivo, $usuarios) {
    $json = json_encode($usuarios, JSON_PRETTY_PRINT);
    file_put_contents($archivo, $json);
}

// ------------------------------------------------------------
// CARGAR USUARIOS DESDE EL JSON
// ------------------------------------------------------------
$usuarios = cargarUsuarios($archivo_usuarios);

// Si no hay usuarios (primera vez o archivo vacío), creamos los de ejemplo
if (empty($usuarios)) {
    $usuarios = [
        'admin' => [
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'rol' => 'admin'
        ],
        'carlos' => [
            'password' => password_hash('usuario123', PASSWORD_DEFAULT),
            'rol' => 'usuario'
        ],
        'invitado' => [
            'password' => password_hash('invitado123', PASSWORD_DEFAULT),
            'rol' => 'invitado'
        ]
    ];
    // Guardamos los usuarios en el JSON por primera vez
    guardarUsuarios($archivo_usuarios, $usuarios);
}

// También guardamos una copia en la sesión para acceso rápido (opcional)
$_SESSION['usuarios'] = $usuarios;

// ------------------------------------------------------------
// CERRAR SESIÓN
// ------------------------------------------------------------
if (isset($_GET['salir'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// ------------------------------------------------------------
// PROCESAR LOGIN
// ------------------------------------------------------------
$error_login = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $pass = $_POST['pass'] ?? '';

    // Verificamos en el array de usuarios (cargado desde JSON)
    if (isset($usuarios[$nombre]) && password_verify($pass, $usuarios[$nombre]['password'])) {
        $_SESSION['usuario_actual'] = $nombre;
        $_SESSION['rol_actual'] = $usuarios[$nombre]['rol'];

        // Redirigir según el rol
        if ($_SESSION['rol_actual'] == 'admin') {
            header('Location: admin_dashboard.php');
        } elseif ($_SESSION['rol_actual'] == 'usuario') {
            header('Location: usuario_dashboard.php');
        } else {
            header('Location: invitado_dashboard.php');
        }
        exit;
    } else {
        $error_login = 'Usuario o contraseña incorrectos';
    }
}

// ------------------------------------------------------------
// PROCESAR CREACIÓN DE NUEVOS USUARIOS (solo admin)
// ------------------------------------------------------------
$mensaje_creacion = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_usuario'])) {
    // Solo el admin logueado puede crear
    if (isset($_SESSION['rol_actual']) && $_SESSION['rol_actual'] == 'admin') {
        $nuevo_nombre = trim($_POST['nuevo_nombre'] ?? '');
        $nueva_pass = $_POST['nueva_pass'] ?? '';
        $nuevo_rol = $_POST['nuevo_rol'] ?? 'usuario';

        if ($nuevo_nombre !== '' && $nueva_pass !== '') {
            if (!isset($usuarios[$nuevo_nombre])) {
                // Agregamos el nuevo usuario al array
                $usuarios[$nuevo_nombre] = [
                    'password' => password_hash($nueva_pass, PASSWORD_DEFAULT),
                    'rol' => $nuevo_rol
                ];
                // Guardamos el array actualizado en el JSON
                guardarUsuarios($archivo_usuarios, $usuarios);
                // También actualizamos la copia en sesión
                $_SESSION['usuarios'] = $usuarios;
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

// ------------------------------------------------------------
// LISTADO DE USUARIOS (solo visible para admin)
// ------------------------------------------------------------
$listado = '';
if (isset($_SESSION['rol_actual']) && $_SESSION['rol_actual'] == 'admin') {
    $listado = '<h3>📋 Usuarios registrados</h3><ul>';
    foreach ($usuarios as $nombre => $datos) {
        $listado .= "<li><strong>$nombre</strong> - Rol: {$datos['rol']}</li>";
    }
    $listado .= '</ul>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login con roles - Con archivo JSON</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 40px auto; background: #f0f0f0; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select, button { width: 100%; padding: 8px; margin: 6px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #2c7da0; color: white; border: none; cursor: pointer; }
        .error { background: #ffe6e6; color: #c00; padding: 8px; border-radius: 6px; }
        .exito { background: #e0ffe0; color: #080; padding: 8px; border-radius: 6px; }
    </style>
</head>
<body>

<?php if (isset($_SESSION['usuario_actual'])): ?>
    <!-- Usuario logueado -->
    <div class="card">
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_actual']); ?></h2>
        <p>Rol: <strong><?php echo $_SESSION['rol_actual']; ?></strong></p>
        <a href="?salir=1" style="display: inline-block; background: #a00; color: white; padding: 6px 12px; text-decoration: none; border-radius: 6px;">Cerrar sesión</a>
    </div>

    <?php if ($_SESSION['rol_actual'] == 'admin'): ?>
        <div class="card">
            <h3>➕ Crear nuevo usuario (se guarda en JSON)</h3>
            <?php if ($mensaje_creacion): ?>
                <div class="<?php echo strpos($mensaje_creacion, '✅') !== false ? 'exito' : 'error'; ?>">
                    <?php echo $mensaje_creacion; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <label>Nombre:</label>
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
            <?php echo $listado; ?>
            <p style="font-size: 0.8em; margin-top: 10px;">📁 Los usuarios se guardan en <strong>usuarios.json</strong></p>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Formulario de login -->
    <div class="card">
        <h2>Iniciar sesión</h2>
        <p><strong>Usuarios de ejemplo (guardados en JSON):</strong></p>
        <ul>
            <li>admin / admin123</li>
            <li>carlos / usuario123</li>
            <li>invitado / invitado123</li>
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
    </div>
<?php endif; ?>

</body>
</html>