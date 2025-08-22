<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

// Aqui procesamos el formulario cuando se envia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if ($username != "" && $email != "" && $password != "") {
    if (strlen($username) >= 3 && strlen($password) >= 6) {
      $conn = Database::connect();

      // Verifica basicamente si el usuario o email ya existen
      $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
      $stmt->bind_param("ss", $username, $email);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
          $success = "Usuario registrado exitosamente.";
        } else {
          $error = "Error al registrar el usuario.";
        }
      } else {
        $error = "El nombre de usuario o email ya están en uso.";
      }
      $conn->close();
    } else {
      $error = "El username debe tener al menos 3 caracteres y la contraseña 6.";
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
  <title>Registrarse</title>
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
    <h2>Registrarse</h2>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form id="registerForm" method="post">
      <label for="username">Nombre de usuario:</label>
      <input type="text" id="username" name="username" required
        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">

      <label for="email">Correo:</label>
      <input type="email" id="email" name="email" required
        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

      <label for="password">Contraseña:</label>
      <input type="password" id="password" name="password" required>

      <button type="submit">Crear cuenta</button>
    </form>
    <p><a href="login.php">Ya tienes cuenta? Inicia sesion</a></p>
  </div>
  <footer>
    <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
  </footer>
  <script src="js/script.js"></script>
</body>

</html>