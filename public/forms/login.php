<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión - UltraZone</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <!-- Header -->
  <header id="header">
    <!-- Top Bar -->
    <div class="top-bar">
      <div class="container">
        <div class="top-bar-item">
          <i class="bi bi-telephone-fill"></i>
          <span>¿Necesitas ayuda? Contáctanos: </span>
          <a href="#">+57 3502296816</a>
        </div>
      </div>
    </div>

    <!-- Main Header -->
    <div class="main-header">
      <div class="main-header-container">
        <div class="header-item">
          <a class="logo" href="../index.html">
            <h1>UltraZone</h1>
          </a>
        </div>
        <form class="search">
          <div class="search-input">
            <input type="text" class="text-search-input" placeholder="Buscar productos">
            <button class="search-button" type="submit">
              <i class="bi bi-search"></i>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Nav Menu -->
    <div class="header-nav">
      <div class="nav-container">
        <nav id="nav-menu" class="nav-menu">
          <ul class="nav-list">
            <li class="nav-item"><a href="../index.html">Inicio</a></li>
            <li class="nav-item"><a href="#">Camisetas</a></li>
            <li class="nav-item"><a href="#">Polo</a></li>
            <li class="nav-item"><a href="#">Caps</a></li>
            <li class="nav-item"><a href="#">Hoodies</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main style="display:flex; justify-content:center; align-items:center; min-height:70vh;">
    <div class="login-container">
      <h2>Iniciar Sesión</h2>
      <form action="authenticate.php" method="post">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Entrar" class="main-item-button">
      </form>
    </div>
  </main>

  <!-- Footer -->
  <footer class="footer-section">
    <div class="footer-content">
      <p>&copy; 2025 UltraZone - Todos los derechos reservados</p>
    </div>
  </footer>

</body>
</html>