<?php
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
        <li><a href="publicar.php">Publicar Alimento</a></li>
        <li><a href="buscar.php">Buscar Alimentos</a></li>
      </ul>
    </nav>
  </header>
  <div class="form-container">
    <h2>Iniciar Sesion</h2>
    <form id="loginForm" action="#" method="post">
      <label for="email">Correo:</label>
      <input type="email" id="email" name="email" required>
      <label for="password">Contraseña:</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Entrar</button>
    </form>
    <p><a href="register.php">¿No tienes cuenta? Registrate</a></p>
  </div>
  <footer>
    <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
  </footer>
  <script src="js/script.js"></script>
</body>
</html>
