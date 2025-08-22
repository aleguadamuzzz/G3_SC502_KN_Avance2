<?php
session_start();
require_once 'config/db.php';

// Si ya esta logueado, redirigir al inicio
if (isset($_SESSION['usuario_id'])) {
  header('Location: index.html');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if ($email != "" && $password != "") {
    $conn = Database::connect();
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    // Si existe el usuario y la clave es correcta
    if ($result && $result['password'] === $password) {
      $_SESSION['usuario_id'] = $result['id'];
      $_SESSION['username'] = $result['username'];
      $_SESSION['email'] = $result['email'];
      $_SESSION['rol'] = $result['rol'];

      header('Location: index.html');
      exit;
    } else {
      $error = "Credenciales incorrectas.";
    }
    $conn->close();
  } else {
    $error = "Todos los campos son obligatorios.";
  }
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesion</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <header>
    <h1>Red de Intercambio de Alimentos</h1>
    <nav>
      <ul>
        <li><a href="index.html">Inicio</a></li>
        <li><a href="login.php">Iniciar Sesion</a></li>
        <li><a href="register.php">Registrarse</a></li>
        <li><a href="buscar.php">Buscar Alimentos</a></li>
      </ul>
    </nav>
  </header>
  <div class="form-container">
    <h2>Iniciar Sesion</h2>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="loginForm" method="post">
      <label for="email">Correo:</label>
      <input type="email" id="email" name="email" required
        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

      <label for="password">Contraseña:</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Entrar</button>
    </form>
    <p><a href="register.php">No tienes cuenta? Registrate</a></p>
  </div>
  <footer>
    <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
  </footer>
  <script src="js/script.js"></script>
</body>

</html>