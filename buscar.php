<?php
session_start();
require_once 'config/db.php';

$conn = Database::connect();

// Obtener todos los alimentos disponibles
$query = "SELECT a.*, u.username FROM alimentos a 
          JOIN usuarios u ON a.usuario_id = u.id 
          WHERE a.estado = 'disponible' 
          ORDER BY a.fecha_publicacion DESC";
$result = $conn->query($query);
$alimentos = $result->fetch_all(MYSQLI_ASSOC);

// Si el usuario está logueado, obtener los alimentos que ya ha reservado para mostrar botón apropiado
$reservados_usuario = [];
if (isset($_SESSION['usuario_id'])) {
    $uid = $_SESSION['usuario_id'];
    $stmtRes = $conn->prepare("SELECT alimento_id FROM reservaciones WHERE usuario_id = ?");
    $stmtRes->bind_param("i", $uid);
    $stmtRes->execute();
    $resList = $stmtRes->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($resList as $r) {
        $reservados_usuario[] = $r['alimento_id'];
    }
    $stmtRes->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Buscar Alimentos</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
  <header>
    <h1>Red de Intercambio de Alimentos</h1>
    <nav>
      <ul>
        <li><a href="index.html">Inicio</a></li>
        <?php if (isset($_SESSION['usuario_id'])): ?>
          <li><a href="publicar.php">Publicar Alimento</a></li>
          <li><a href="panel_usuario.php">Mi Panel</a></li>
          <li><a href="logout.php">Cerrar Sesión (<?= $_SESSION['username'] ?>)</a></li>
          <?php if ($_SESSION['rol'] === 'admin'): ?>
            <li><a href="admin.php">Panel Admin</a></li>
          <?php endif; ?>
        <?php else: ?>
          <li><a href="login.php">Iniciar Sesion</a></li>
          <li><a href="register.php">Registrarse</a></li>
        <?php endif; ?>
        <li><a href="buscar.php">Buscar Alimentos</a></li>
      </ul>
    </nav>
  </header>

  <!-- Hero Section -->
  <div class="container-fluid bg-light py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <h2 class="display-4 fw-bold text-success mb-4">Encuentra Alimentos Cerca de Ti</h2>
          <p class="lead text-muted">Descubre alimentos frescos disponibles en tu comunidad y ayuda a reducir el
            desperdicio alimentario.</p>
        </div>
        <div class="col-lg-6">
          <div class="ratio ratio-16x9 rounded shadow-lg overflow-hidden">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d15720.34880900993!2d-84.0488768395875!3d9.926695496418306!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2scr!4v1752260238583!5m2!1ses!2scr"
              allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Alimentos Section -->
  <div class="container my-5">
    <div class="row mb-4">
      <div class="col-12 text-center">
        <h3 class="display-5 fw-bold text-dark">
          <i class="fas fa-utensils text-success me-3"></i>Alimentos Disponibles
        </h3>
        <p class="text-muted">Productos frescos compartidos por nuestra comunidad</p>
        <hr class="w-25 mx-auto border-success border-3">
      </div>
    </div>

    <?php if (empty($alimentos)): ?>
      <div class="row">
        <div class="col-12">
          <div class="alert alert-info text-center py-5">
            <i class="fas fa-info-circle fa-3x mb-3 text-info"></i>
            <h4>No hay alimentos disponibles</h4>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($alimentos as $alimento): ?>
          <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden" style="transition: all 0.3s ease;">

              <div class="position-relative">
                <?php
                $imagen_src = '';
                if ($alimento['imagen']) {
                  // Si es URL web (contiene http) como tal.
                  if (strpos($alimento['imagen'], 'http') === 0) {
                    $imagen_src = $alimento['imagen'];
                  }
                  // Si es archivo local entonces nada mas con el directorio.
                  elseif (file_exists($alimento['imagen'])) {
                    $imagen_src = $alimento['imagen'];
                  }
                }
                ?>

                <?php if ($imagen_src): ?>
                  <img src="<?= htmlspecialchars($imagen_src) ?>" alt="<?= htmlspecialchars($alimento['nombre']) ?>"
                    class="card-img-top" style="height: 250px; object-fit: cover;">
                <?php else: ?>
                  <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                    <i class="fas fa-image fa-3x text-muted"></i>
                  </div>
                <?php endif; ?>

                <?php
                $dias_hasta_caducidad = (strtotime($alimento['fecha_caducidad']) - time()) / (60 * 60 * 24);
                $badge_class = 'bg-success';
                $badge_text = 'Disponible';
                $badge_icon = 'fas fa-check-circle';

                if ($dias_hasta_caducidad <= 0) {
                  $badge_class = 'bg-danger';
                  $badge_text = 'Vencido';
                  $badge_icon = 'fas fa-exclamation-triangle';
                } elseif ($dias_hasta_caducidad <= 2) {
                  $badge_class = 'bg-warning text-dark';
                  $badge_text = 'Vence pronto';
                  $badge_icon = 'fas fa-clock';
                } elseif ($dias_hasta_caducidad <= 5) {
                  $badge_class = 'bg-info';
                  $badge_text = ceil($dias_hasta_caducidad) . ' días';
                  $badge_icon = 'fas fa-hourglass-half';
                }
                ?>
                <span class="badge <?= $badge_class ?> position-absolute top-0 start-0 m-3">
                  <i class="<?= $badge_icon ?> me-1"></i><?= $badge_text ?>
                </span>
              </div>

              <div class="card-body d-flex flex-column">
                <div class="mb-3">
                  <h5 class="card-title fw-bold text-success mb-2">
                    <i class="fas fa-apple-alt me-2"></i><?= htmlspecialchars($alimento['nombre']) ?>
                  </h5>
                  <p class="card-text text-muted small">
                    <?= htmlspecialchars($alimento['descripcion']) ?>
                  </p>
                </div>

                <div class="mt-auto">
                  <div class="row g-2 mb-3">
                    <div class="col-6">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        <small class="text-muted">
                          <strong>Caduca:</strong><br>
                          <?= date('d/m/Y', strtotime($alimento['fecha_caducidad'])) ?>
                        </small>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <small class="text-muted">
                          <strong>Ubicacion:</strong><br>
                          <?= htmlspecialchars($alimento['ubicacion']) ?>
                        </small>
                      </div>
                    </div>
                  </div>

                  <div class="row g-2">
                    <div class="col-6">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-user text-info me-2"></i>
                        <small class="text-muted">
                          <strong>Por:</strong><br>
                          <?= htmlspecialchars($alimento['username']) ?>
                        </small>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-clock text-secondary me-2"></i>
                        <small class="text-muted">
                          <strong>Publicado:</strong><br>
                          <?= date('d/m H:i', strtotime($alimento['fecha_publicacion'])) ?>
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card-footer bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                  <small class="text-muted">
                    <i class="fas fa-share-alt me-1"></i>
                    Intercambio disponible
                  </small>
                  <?php
                  // Determinar qué acción mostrar en función del estado de la reserva y del usuario
                  $isOwnerCard = (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $alimento['usuario_id']);
                  $isReservedByUser = (isset($_SESSION['usuario_id']) && in_array($alimento['id'], $reservados_usuario));
                  ?>
                  <?php if (!isset($_SESSION['usuario_id'])): ?>
                    <a href="login.php" class="btn btn-sm btn-outline-success">
                      <i class="fas fa-sign-in-alt me-1"></i>Inicia sesión
                    </a>
                  <?php elseif ($isOwnerCard): ?>
                    <span class="badge bg-secondary">Es tu publicación</span>
                  <?php elseif ($isReservedByUser): ?>
                    <a href="mensaje.php?alimento_id=<?= $alimento['id'] ?>" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-comments me-1"></i>Ver chat
                    </a>
                  <?php else: ?>
                    <a href="mensaje.php?alimento_id=<?= $alimento['id'] ?>" class="btn btn-sm btn-outline-success">
                      <i class="fas fa-envelope me-1"></i>Reservar y contactar
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <footer>
    <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
  </footer>

  <script src="js/script.js"></script>
</body>

</html>