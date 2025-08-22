<?php
session_start();
require_once 'config/db.php';

// Verificar si esta logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Procesar la publicacion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_caducidad = $_POST['caducidad'];
    $ubicacion = trim($_POST['ubicacion']);
    
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $nuevo_nombre = time() . '_' . uniqid() . '.' . $file_extension;
            $imagen = $upload_dir . $nuevo_nombre;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen)) {
                // Imagen subida correctamente
            } else {
                $imagen = ''; // Error al subir
            }
        }
    }

    if ($nombre != "" && $descripcion != "" && $fecha_caducidad != "" && $ubicacion != "") {
        if (strlen($descripcion) >= 5) {
            $conn = Database::connect();
            
            $stmt = $conn->prepare("INSERT INTO alimentos (nombre, descripcion, fecha_caducidad, imagen, ubicacion, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $nombre, $descripcion, $fecha_caducidad, $imagen, $ubicacion, $_SESSION['usuario_id']);
            
            if ($stmt->execute()) {
                $success = "¡Alimento publicado exitosamente!";
                // Limpiar el formulario
                $_POST = array();

                // Enviar notificaciones a los demas usuarios sobre este nuevo alimento
                // La notificacion advierte sobre un nuevo alimento disponible en cierta ubicacion.
                $alimento_id = $stmt->insert_id;
                $nombre_alimento = $nombre;
                $ubicacion_alimento = $ubicacion;
                // Obtener todos los usuarios excepto el que publica
                $usuarios_stmt = $conn->prepare("SELECT id FROM usuarios WHERE id != ?");
                $usuarios_stmt->bind_param("i", $_SESSION['usuario_id']);
                $usuarios_stmt->execute();
                $usuarios_result = $usuarios_stmt->get_result();
                $notif_stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje) VALUES (?, ?)");
                while ($row = $usuarios_result->fetch_assoc()) {
                    $uid = $row['id'];
                    $mensaje_notif = "Nuevo alimento disponible: " . $nombre_alimento . " en " . $ubicacion_alimento;
                    $notif_stmt->bind_param("is", $uid, $mensaje_notif);
                    $notif_stmt->execute();
                }
                $usuarios_stmt->close();
                $notif_stmt->close();
            } else {
                $error = "Error al publicar el alimento.";
            }
            $conn->close();
        } else {
            $error = "La descripción debe tener al menos 5 caracteres.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Publicar Alimento</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header>
    <h1>Red de Intercambio de Alimentos</h1>
    <nav>
      <ul>
        <li><a href="index.html">Inicio</a></li>
        <li><a href="publicar.php">Publicar Alimento</a></li>
        <li><a href="buscar.php">Buscar Alimentos</a></li>
        <li><a href="panel_usuario.php">Mi Panel</a></li>
        <li><a href="logout.php">Cerrar Sesión (<?= $_SESSION['username'] ?>)</a></li>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <li><a href="admin.php">Panel Admin</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>
  <div class="form-container">
    <h2>Publicar Alimento</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form id="publicarForm" method="post" enctype="multipart/form-data">
      <label for="nombre">Nombre del alimento:</label>
      <input type="text" id="nombre" name="nombre" required value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
      
      <label for="descripcion">Descripcion:</label>
      <textarea id="descripcion" name="descripcion" rows="3" required><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
      
      <label for="caducidad">Fecha de caducidad:</label>
      <input type="date" id="caducidad" name="caducidad" required value="<?= isset($_POST['caducidad']) ? $_POST['caducidad'] : '' ?>">
      
      <label for="imagen">Imagen:</label>
      <input type="file" id="imagen" name="imagen" accept="image/*">
      
      <label for="ubicacion">Ubicacion:</label>
      <input type="text" id="ubicacion" name="ubicacion" required value="<?= isset($_POST['ubicacion']) ? htmlspecialchars($_POST['ubicacion']) : '' ?>">
      
      <button type="submit">Publicar</button>
    </form>
  </div>
  <footer>
    <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
  </footer>
  <script src="js/script.js"></script>
</body>
</html>