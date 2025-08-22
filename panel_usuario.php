<?php
// Panel de usuario para ver publicaciones, reservas, notificaciones y configuraciones
session_start();
require_once 'config/db.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$conn = Database::connect();
$user_id = $_SESSION['usuario_id'];

// Obtener las publicaciones del usuario
$stmt = $conn->prepare("SELECT * FROM alimentos WHERE usuario_id = ? ORDER BY fecha_publicacion DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$publicaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener las reservas realizadas por el usuario
$sqlReservas = "SELECT r.*, a.nombre, a.imagen, a.fecha_caducidad, a.ubicacion, u.username AS propietario
                FROM reservaciones r
                JOIN alimentos a ON r.alimento_id = a.id
                JOIN usuarios u ON a.usuario_id = u.id
                WHERE r.usuario_id = ?
                ORDER BY r.fecha_reserva DESC";
$stmtReserva = $conn->prepare($sqlReservas);
$stmtReserva->bind_param("i", $user_id);
$stmtReserva->execute();
$reservas = $stmtReserva->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtReserva->close();

// Obtener las notificaciones del usuario
$stmtNotif = $conn->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC");
$stmtNotif->bind_param("i", $user_id);
$stmtNotif->execute();
$notificaciones = $stmtNotif->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtNotif->close();

// Marcar notificaciones como leídas
$conn->query("UPDATE notificaciones SET leido = 1 WHERE usuario_id = $user_id");

// Manejar actualización de configuraciones
$configError = '';
$configSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $nuevoUsername = trim($_POST['username']);
    $nuevoEmail = trim($_POST['email']);
    $nuevoPassword = trim($_POST['password']);

    if ($nuevoUsername !== '' && $nuevoEmail !== '') {
        // Actualizar contraseña solo si se ingresó
        if ($nuevoPassword !== '') {
            $updateStmt = $conn->prepare("UPDATE usuarios SET username=?, email=?, password=? WHERE id=?");
            $updateStmt->bind_param("sssi", $nuevoUsername, $nuevoEmail, $nuevoPassword, $user_id);
        } else {
            $updateStmt = $conn->prepare("UPDATE usuarios SET username=?, email=? WHERE id=?");
            $updateStmt->bind_param("ssi", $nuevoUsername, $nuevoEmail, $user_id);
        }
        if ($updateStmt->execute()) {
            $configSuccess = 'Datos actualizados correctamente.';
            $_SESSION['username'] = $nuevoUsername;
            $_SESSION['email'] = $nuevoEmail;
        } else {
            $configError = 'Error al actualizar los datos.';
        }
        $updateStmt->close();
    } else {
        $configError = 'Todos los campos son obligatorios.';
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Panel</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <header>
        <h1>Panel de Usuario</h1>
        <nav>
            <ul>
                <li><a href="index.html">Inicio</a></li>
                <li><a href="publicar.php">Publicar Alimento</a></li>
                <li><a href="buscar.php">Buscar Alimentos</a></li>
                <li><a href="panel_usuario.php">Mi Panel</a></li>
                <li><a href="logout.php">Cerrar Sesión (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <li><a href="admin.php">Panel Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container my-4">
        <div class="row">
            <div class="col-12 mb-4">
                <h2 class="fw-bold">Mis Publicaciones</h2>
                <?php if (empty($publicaciones)): ?>
                    <p>No has publicado ningún alimento.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Caducidad</th>
                                    <th>Estado</th>
                                    <th>Publicado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($publicaciones as $pub): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($pub['nombre']) ?></td>
                                        <td><?= htmlspecialchars(substr($pub['descripcion'], 0, 50)) ?>...</td>
                                        <td><?= date('d/m/Y', strtotime($pub['fecha_caducidad'])) ?></td>
                                        <td><span class="badge <?= $pub['estado'] == 'disponible' ? 'bg-success' : 'bg-secondary' ?>"><?= $pub['estado'] ?></span></td>
                                        <td><?= date('d/m/Y H:i', strtotime($pub['fecha_publicacion'])) ?></td>
                                        <td>
                                            <!-- Enlace para ver los mensajes de este alimento -->
                                            <a href="mensaje.php?alimento_id=<?= $pub['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comments"></i> Ver chat
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12 mb-4">
                <h2 class="fw-bold">Mis Reservas</h2>
                <?php if (empty($reservas)): ?>
                    <p>No has reservado ningún alimento.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Alimento</th>
                                    <th>Propietario</th>
                                    <th>Ubicación</th>
                                    <th>Caducidad</th>
                                    <th>Reservado el</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $res): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($res['nombre']) ?></td>
                                        <td><?= htmlspecialchars($res['propietario']) ?></td>
                                        <td><?= htmlspecialchars($res['ubicacion']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($res['fecha_caducidad'])) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($res['fecha_reserva'])) ?></td>
                                        <td><span class="badge <?= $res['estado'] == 'pendiente' ? 'bg-warning' : ($res['estado']=='aceptada' ? 'bg-success' : 'bg-secondary') ?>"><?= $res['estado'] ?></span></td>
                                        <td>
                                            <a href="mensaje.php?alimento_id=<?= $res['alimento_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-comments"></i> Ver chat
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12 mb-4">
                <h2 class="fw-bold">Notificaciones</h2>
                <?php if (empty($notificaciones)): ?>
                    <p>No tienes notificaciones.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($notificaciones as $notif): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($notif['mensaje']) ?>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notif['fecha'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="col-12 mb-4">
                <h2 class="fw-bold">Configuración</h2>
                <?php if ($configError): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($configError) ?></div>
                <?php endif; ?>
                <?php if ($configSuccess): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($configSuccess) ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3">
                    <input type="hidden" name="update_settings" value="1">
                    <div class="col-md-4">
                        <label for="username" class="form-label">Nombre de usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?= htmlspecialchars($_SESSION['username']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_SESSION['email']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="password" class="form-label">Nueva contraseña (opcional)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer>
        <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
    </footer>
</body>

</html>