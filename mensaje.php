<?php
// Página para gestionar la comunicación entre usuarios en torno a un alimento y crear reservas.
session_start();
require_once 'config/db.php';

// Verificar login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$conn = Database::connect();
$current_id = $_SESSION['usuario_id'];

// Validar parámetro de alimento
$alimento_id = isset($_GET['alimento_id']) ? intval($_GET['alimento_id']) : 0;
if ($alimento_id <= 0) {
    header('Location: buscar.php');
    exit;
}

// Obtener información del alimento y su propietario
$stmt = $conn->prepare(
    "SELECT a.*, u.username AS owner_username, u.id AS owner_id FROM alimentos a JOIN usuarios u ON a.usuario_id = u.id WHERE a.id = ?"
);
$stmt->bind_param("i", $alimento_id);
$stmt->execute();
$alimento = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$alimento) {
    // Alimento no existe
    $conn->close();
    echo "<p>Alimento no encontrado.</p>";
    exit;
}

$owner_id = intval($alimento['usuario_id']);

// Determinar el destinatario (partner) de la conversación
// Si el usuario no es el propietario, el destinatario es el propietario.
// Si el usuario es el propietario, debe especificarse el destinatario mediante GET o elegirlo de las reservas.
$partner_id = 0;
if ($current_id !== $owner_id) {
    $partner_id = $owner_id;
    // Comprobar o crear reservación si no existe
    $resStmt = $conn->prepare("SELECT * FROM reservaciones WHERE alimento_id = ? AND usuario_id = ?");
    $resStmt->bind_param("ii", $alimento_id, $current_id);
    $resStmt->execute();
    $reserva = $resStmt->get_result()->fetch_assoc();
    $resStmt->close();
    if (!$reserva) {
        // Crear nueva reserva en estado pendiente
        $insRes = $conn->prepare("INSERT INTO reservaciones (alimento_id, usuario_id) VALUES (?, ?)");
        $insRes->bind_param("ii", $alimento_id, $current_id);
        $insRes->execute();
        $insRes->close();
        // Generar notificación para el propietario
        $msg_notif = "Tu alimento " . $alimento['nombre'] . " ha sido reservado por " . $_SESSION['username'];
        $notifStmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje) VALUES (?, ?)");
        $notifStmt->bind_param("is", $owner_id, $msg_notif);
        $notifStmt->execute();
        $notifStmt->close();
    }
} else {
    // El usuario es el propietario. Ver si se especifica un destinatario por GET
    if (isset($_GET['destinatario'])) {
        $partner_id = intval($_GET['destinatario']);
        // Validar que el destinatario tenga una reserva sobre este alimento
        $checkStmt = $conn->prepare("SELECT 1 FROM reservaciones WHERE alimento_id = ? AND usuario_id = ?");
        $checkStmt->bind_param("ii", $alimento_id, $partner_id);
        $checkStmt->execute();
        $existe = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();
        if (!$existe) {
            $partner_id = 0;
        }
    }
}

// Procesar envío de mensaje
$mensajeError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_mensaje'])) {
    $contenido = trim($_POST['contenido']);
    $dest_id_post = intval($_POST['destinatario']);
    if ($contenido === '') {
        $mensajeError = 'El mensaje no puede estar vacío.';
    } elseif ($dest_id_post <= 0) {
        $mensajeError = 'Destinatario inválido.';
    } else {
        // Insertar el mensaje
        $msgStmt = $conn->prepare("INSERT INTO mensajes (alimento_id, remitente_id, destinatario_id, contenido) VALUES (?, ?, ?, ?)");
        $msgStmt->bind_param("iiis", $alimento_id, $current_id, $dest_id_post, $contenido);
        $msgStmt->execute();
        $msgStmt->close();
        // Crear notificación para el destinatario
        $notifTxt = "Nuevo mensaje sobre " . $alimento['nombre'];
        $notifIns = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje) VALUES (?, ?)");
        $notifIns->bind_param("is", $dest_id_post, $notifTxt);
        $notifIns->execute();
        $notifIns->close();
        // Redireccionar a la misma página para evitar reenvíos al refrescar
        header("Location: mensaje.php?alimento_id=" . $alimento_id . ($current_id == $owner_id ? '&destinatario=' . $dest_id_post : ''));
        exit;
    }
}

// Cargar mensajes si ya hay partner
$mensajes = [];
if ($partner_id > 0) {
    $msgQuery = $conn->prepare(
        "SELECT m.*, u.username AS remitente_nombre FROM mensajes m JOIN usuarios u ON m.remitente_id = u.id " .
        "WHERE m.alimento_id = ? AND ((m.remitente_id = ? AND m.destinatario_id = ?) OR (m.remitente_id = ? AND m.destinatario_id = ?)) ORDER BY m.fecha_envio ASC"
    );
    $msgQuery->bind_param("iiiii", $alimento_id, $current_id, $partner_id, $partner_id, $current_id);
    $msgQuery->execute();
    $mensajes = $msgQuery->get_result()->fetch_all(MYSQLI_ASSOC);
    $msgQuery->close();
    // Marcar mensajes recibidos como leídos
    $updateRead = $conn->prepare("UPDATE mensajes SET leido = 1 WHERE alimento_id = ? AND remitente_id = ? AND destinatario_id = ? AND leido = 0");
    $updateRead->bind_param("iii", $alimento_id, $partner_id, $current_id);
    $updateRead->execute();
    $updateRead->close();

    // Obtener el nombre del destinatario para mostrarlo en el encabezado
    $pnameStmt = $conn->prepare("SELECT username FROM usuarios WHERE id = ?");
    $pnameStmt->bind_param("i", $partner_id);
    $pnameStmt->execute();
    $pnameRes = $pnameStmt->get_result()->fetch_assoc();
    $partner_name = $pnameRes ? $pnameRes['username'] : '';
    $pnameStmt->close();
}

// Si el usuario es propietario y no se ha seleccionado destinatario, obtener lista de reservistas
$reservistas = [];
if ($current_id === $owner_id && $partner_id <= 0) {
    $resList = $conn->prepare("SELECT r.usuario_id, u.username FROM reservaciones r JOIN usuarios u ON r.usuario_id = u.id WHERE r.alimento_id = ?");
    $resList->bind_param("i", $alimento_id);
    $resList->execute();
    $reservistas = $resList->get_result()->fetch_all(MYSQLI_ASSOC);
    $resList->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensajes - <?= htmlspecialchars($alimento['nombre']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <h1>Comunicación</h1>
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
            <div class="col-12 mb-3">
                <h2 class="fw-bold">Alimento: <?= htmlspecialchars($alimento['nombre']) ?></h2>
                <p><strong>Descripción:</strong> <?= htmlspecialchars($alimento['descripcion']) ?></p>
                <p><strong>Ubicación:</strong> <?= htmlspecialchars($alimento['ubicacion']) ?></p>
                <p><strong>Caducidad:</strong> <?= date('d/m/Y', strtotime($alimento['fecha_caducidad'])) ?></p>
            </div>
            <?php if ($current_id === $owner_id && $partner_id <= 0): ?>
                <!-- Lista de reservistas para elegir conversar -->
                <div class="col-12">
                    <h3 class="fw-bold">Reservas realizadas</h3>
                    <?php if (empty($reservistas)): ?>
                        <p>No hay reservas para este alimento aún.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($reservistas as $res): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($res['username']) ?>
                                    <a href="mensaje.php?alimento_id=<?= $alimento_id ?>&destinatario=<?= $res['usuario_id'] ?>" class="btn btn-sm btn-primary">Abrir chat</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Conversación con el destinatario -->
                <div class="col-12">
                    <?php if ($partner_id <= 0): ?>
                        <div class="alert alert-info">Seleccione un destinatario para iniciar la conversación.</div>
                    <?php else: ?>
                        <h3 class="fw-bold">Conversación con 
                            <?php
                            // Mostrar el nombre del participante en la conversación.
                            // Si el usuario no es el propietario, se muestra el propietario del alimento.
                            // Si el usuario es el propietario, se muestra el nombre del reservista seleccionado.
                            if ($current_id !== $owner_id) {
                                echo htmlspecialchars($alimento['owner_username']);
                            } else {
                                echo htmlspecialchars($partner_name ?? '');
                            }
                            ?>
                        </h3>
                        <div class="border rounded p-3 mb-3" style="height: 300px; overflow-y: auto; background-color: #f7f7f7;">
                            <?php if (empty($mensajes)): ?>
                                <p>No hay mensajes aún.</p>
                            <?php else: ?>
                                <?php foreach ($mensajes as $msg): ?>
                                    <div class="mb-2">
                                        <strong><?= htmlspecialchars($msg['remitente_nombre']) ?>:</strong>
                                        <span><?= nl2br(htmlspecialchars($msg['contenido'])) ?></span><br>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($msg['fecha_envio'])) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($partner_id > 0): ?>
                            <?php if ($mensajeError): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($mensajeError) ?>
                                </div>
                            <?php endif; ?>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="destinatario" value="<?= $partner_id ?>">
                                <textarea name="contenido" class="form-control" rows="2" placeholder="Escribe un mensaje..."></textarea>
                                <button type="submit" name="enviar_mensaje" class="btn btn-success">Enviar</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
    </footer>
</body>
</html>