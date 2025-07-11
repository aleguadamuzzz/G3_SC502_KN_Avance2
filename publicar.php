<?php
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
        <li><a href="login.php">Iniciar Sesion</a></li>
        <li><a href="register.php">Registrarse</a></li>
        <li><a href="publicar.php">Publicar Alimento</a></li>
        <li><a href="buscar.php">Buscar Alimentos</a></li>
      </ul>
    </nav>
  </header>
  <div class="form-container">
    <h2>Publicar Alimento</h2>
    <form id="publicarForm" action="#" method="post">
      <label for="nombre">Nombre del alimento:</label>
      <input type="text" id="nombre" name="nombre" required>
      <label for="descripcion">Descripcion:</label>
      <textarea id="descripcion" name="descripcion" rows="3"></textarea>
      <label for="caducidad">Fecha de caducidad:</label>
      <input type="date" id="caducidad" name="caducidad">
      <label for="imagen">Imagen:</label>
      <input type="file" id="imagen" name="imagen">
      <label for="ubicacion">Ubicacion:</label>
      <input type="text" id="ubicacion" name="ubicacion">
      <button type="submit">Publicar</button>
    </form>
  </div>
  <footer>
    <p>© 2025 Grupo G3 - Universidad Fidelitas</p>
  </footer>
  <script src="js/script.js"></script>
</body>
</html>
