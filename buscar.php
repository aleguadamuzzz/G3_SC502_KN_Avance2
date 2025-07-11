<?php
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Buscar Alimentos</title>
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
  <h2 style="text-align: center;">Buscar Alimentos Cercanos</h2>
  <div class="mapa">
    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d15720.34880900993!2d-84.0488768395875!3d9.926695496418306!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ses!2scr!4v1752260238583!5m2!1ses!2scr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
  <ul class="lista-alimentos">
    <li>Pan integral - 500m</li>
    <li>Leche - 1km</li>
    <li>Frutas mixtas - 750m</li>
  </ul>
  <footer>
    <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
  </footer>
  <script src="js/script.js"></script>
</body>
</html>
