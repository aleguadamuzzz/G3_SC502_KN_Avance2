<?php
session_start();
require_once 'config/db.php';

// Verificar si realmente esta logueado y es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = Database::connect();
$message = '';

if (isset($_GET['eliminar_usuario'])) {
    $id = intval($_GET['eliminar_usuario']);
    if ($id != $_SESSION['usuario_id']) { // Evidentemente no puede eliminarse a si mismo
        $conn->query("DELETE FROM usuarios WHERE id = $id");
        $message = "Usuario eliminado correctamente.";
    }
}

if (isset($_GET['eliminar_alimento'])) {
    $id = intval($_GET['eliminar_alimento']);
    $conn->query("DELETE FROM alimentos WHERE id = $id");
    $message = "Alimento eliminado correctamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];

    if ($username && $email) {
        $stmt = $conn->prepare("UPDATE usuarios SET username=?, email=?, rol=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $rol, $id);
        $stmt->execute();
        $message = "Usuario actualizado correctamente.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_alimento'])) {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_caducidad = $_POST['fecha_caducidad'];
    $ubicacion = trim($_POST['ubicacion']);
    $estado = $_POST['estado'];

    if ($nombre && $descripcion && $fecha_caducidad && $ubicacion) {
        $stmt = $conn->prepare("UPDATE alimentos SET nombre=?, descripcion=?, fecha_caducidad=?, ubicacion=?, estado=? WHERE id=?");
        $stmt->bind_param("sssssi", $nombre, $descripcion, $fecha_caducidad, $ubicacion, $estado, $id);
        $stmt->execute();
        $message = "Alimento actualizado correctamente.";
    }
}

$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY id")->fetch_all(MYSQLI_ASSOC);

$alimentos = $conn->query("SELECT a.*, u.username FROM alimentos a JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.fecha_publicacion DESC")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Panel de Administracion</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <header>
        <h1>Panel de Administracion</h1>
        <nav>
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="publicar.php">Publicar Alimento</a></li>
                <li><a href="buscar.php">Buscar Alimentos</a></li>
                <li><a href="panel_usuario.php">Mi Panel</a></li>
                <li><a href="logout.php">Cerrar Sesión (<?= $_SESSION['username'] ?>)</a></li>
            </ul>
        </nav>
    </header>

    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Gestion de Usuarios</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= $usuario['id'] ?></td>
                                            <td><?= htmlspecialchars($usuario['username']) ?></td>
                                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                                            <td><?= $usuario['rol'] ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#modalEditarUsuario<?= $usuario['id'] ?>">Editar</button>
                                                <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                                    <a href="?eliminar_usuario=<?= $usuario['id'] ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="modalEditarUsuario<?= $usuario['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Usuario</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                            <div class="mb-3">
                                                                <label>Username:</label>
                                                                <input type="text" name="username" class="form-control"
                                                                    value="<?= htmlspecialchars($usuario['username']) ?>"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Email:</label>
                                                                <input type="email" name="email" class="form-control"
                                                                    value="<?= htmlspecialchars($usuario['email']) ?>"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Rol:</label>
                                                                <select name="rol" class="form-control" required>
                                                                    <option value="usuario" <?= $usuario['rol'] == 'usuario' ? 'selected' : '' ?>>Usuario</option>
                                                                    <option value="admin" <?= $usuario['rol'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cerrar</button>
                                                            <button type="submit" name="editar_usuario"
                                                                class="btn btn-primary">Guardar cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Gestion de Alimentos</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Usuario</th>
                                        <th>Ubicación</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alimentos as $alimento): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($alimento['nombre']) ?></td>
                                            <td><?= htmlspecialchars($alimento['username']) ?></td>
                                            <td><?= htmlspecialchars($alimento['ubicacion']) ?></td>
                                            <td>
                                                <span
                                                    class="badge <?= $alimento['estado'] == 'disponible' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $alimento['estado'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#modalEditarAlimento<?= $alimento['id'] ?>">Editar</button>
                                                <a href="?eliminar_alimento=<?= $alimento['id'] ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Eliminar alimento?')">Eliminar</a>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="modalEditarAlimento<?= $alimento['id'] ?>"
                                            tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Alimento</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $alimento['id'] ?>">
                                                            <div class="mb-3">
                                                                <label>Nombre:</label>
                                                                <input type="text" name="nombre" class="form-control"
                                                                    value="<?= htmlspecialchars($alimento['nombre']) ?>"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Descripción:</label>
                                                                <textarea name="descripcion" class="form-control"
                                                                    required><?= htmlspecialchars($alimento['descripcion']) ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Fecha de caducidad:</label>
                                                                <input type="date" name="fecha_caducidad"
                                                                    class="form-control"
                                                                    value="<?= $alimento['fecha_caducidad'] ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Ubicación:</label>
                                                                <input type="text" name="ubicacion" class="form-control"
                                                                    value="<?= htmlspecialchars($alimento['ubicacion']) ?>"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Estado:</label>
                                                                <select name="estado" class="form-control" required>
                                                                    <option value="disponible"
                                                                        <?= $alimento['estado'] == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                                                    <option value="intercambiado"
                                                                        <?= $alimento['estado'] == 'intercambiado' ? 'selected' : '' ?>>Intercambiado</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cerrar</button>
                                                            <button type="submit" name="editar_alimento"
                                                                class="btn btn-primary">Guardar cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>