<?php
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
        <li><a href="publicar.php">Publicar Alimento</a></li>
        <li><a href="buscar.php">Buscar Alimentos</a></li>
      </ul>
    </nav>
  </header>
  <div class="form-container">
    <h2>Registrarse</h2>
    <form id="registerForm" action="#" method="post">
      <label for="username">Nombre de usuario:</label>
      <input type="text" id="username" name="username" required>
      <label for="email">Correo:</label>
      <input type="email" id="email" name="email" required>
      <label for="password">Contraseña:</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Crear cuenta</button>
    </form>
    <p><a href="login.php">¿Ya tienes cuenta? Inicia sesion</a></p>
  </div>
  <footer>
    <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
  </footer>
  <script src="js/script.js"></script>
</body>
</html>
