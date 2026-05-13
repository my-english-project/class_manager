<?php
/**
 * ClassHub — Login View
 * Rediseñado Mod 9: Login y Registro
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ClassHub — Inicia sesión en el Sistema de Administración de Clase de la UTS">
  <title>ClassHub — Iniciar Sesión | UTS</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="icon" type="image/png" href="assets/img/logo-uts-2024.png">
  <style>
    .panel-hidden { display: none; }
    .alert { padding: var(--space-3); margin-bottom: var(--space-4); border-radius: var(--radius-sm); font-size: var(--text-sm); font-weight: 500; display: none; text-align: left; }
    .alert-error { background: rgba(235,87,87,0.1); border: 1px solid rgba(235,87,87,0.2); color: var(--color-danger); }
    .alert-success { background: rgba(39,174,96,0.1); border: 1px solid rgba(39,174,96,0.2); color: var(--color-success); }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card">
      <img src="assets/img/logo-uts-2024.png" alt="Universidad Tecnológica de Salamanca" class="login-logo">
      
      <h1 class="login-heading">ClassHub</h1>
      <p class="login-sub">Sistema de Administración de Clase</p>

      <div id="alert-box" class="alert"></div>

      <!-- Login Form -->
      <div id="panel-login">
        <form onsubmit="doLogin(event)">
          <div class="form-group" style="text-align: left;">
            <label class="form-label" for="username">Usuario (@utsalamanca.edu.mx)</label>
            <input type="text" class="form-control" id="username" name="username" required autocomplete="username" placeholder="Ej. 1808005@utsalamanca.edu.mx">
          </div>
          <div class="form-group" style="text-align: left;">
            <label class="form-label" for="password">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
          </div>
          <button type="submit" class="btn btn-primary btn-block" id="btn-login" style="margin-top: var(--space-4);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
              <polyline points="10 17 15 12 10 7"/>
              <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            Iniciar Sesión
          </button>
        </form>
        <div class="login-divider">o</div>
        <button type="button" class="btn btn-outline btn-block" onclick="togglePanels('register')">
          Registrarse en el sistema
        </button>
      </div>

      <!-- Register Form -->
      <div id="panel-register" class="panel-hidden">
        <form onsubmit="doRegister(event)">
          <div class="form-group" style="text-align: left;">
            <label class="form-label" for="matricula-reg">Matrícula Institucional</label>
            <input type="text" class="form-control" id="matricula-reg" name="matricula" required placeholder="Docente o Alumno">
          </div>
          <button type="submit" class="btn btn-primary btn-block" id="btn-register" style="margin-top: var(--space-4);">
            Validar y Registrarse
          </button>
        </form>
        <div class="login-divider">Ya tengo cuenta</div>
        <button type="button" class="btn btn-outline btn-block" onclick="togglePanels('login')">
          Volver a Iniciar Sesión
        </button>
      </div>

      <p style="margin-top: var(--space-6); font-size: var(--text-xs); color: var(--gray-400);">
        Universidad Tecnológica de Salamanca · v1.1
      </p>
    </div>
  </div>

  <script>
    function showAlert(msg, isSuccess = false) {
      const box = document.getElementById('alert-box');
      box.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-error');
      box.innerHTML = msg;
      box.style.display = 'block';
    }

    function togglePanels(panel) {
      document.getElementById('alert-box').style.display = 'none';
      if (panel === 'register') {
        document.getElementById('panel-login').classList.add('panel-hidden');
        document.getElementById('panel-register').classList.remove('panel-hidden');
      } else {
        document.getElementById('panel-register').classList.add('panel-hidden');
        document.getElementById('panel-login').classList.remove('panel-hidden');
      }
    }

    function doLogin(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-login');
      btn.disabled = true;
      btn.innerHTML = 'Conectando...';
      
      const formData = new FormData(e.target);
      fetch('index.php?action=login', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            window.location.href = data.redirect;
          } else {
            showAlert(data.message || 'Error al acceder');
            btn.disabled = false;
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> Iniciar Sesión';
          }
        }).catch(() => {
          showAlert('Error de red. Verifica la conexión.');
          btn.disabled = false;
          btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> Iniciar Sesión';
        });
    }

    function doRegister(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-register');
      btn.disabled = true;
      btn.innerHTML = 'Verificando...';
      
      const formData = new FormData(e.target);
      fetch('index.php?action=register', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          btn.disabled = false;
          btn.innerHTML = 'Validar y Registrarse';
          if (data.success) {
            showAlert(data.message, true);
            e.target.reset();
            // Optional: Automatically copy matricula to login username field
            const mat = formData.get('matricula');
            document.getElementById('username').value = mat + '@utsalamanca.edu.mx';
            setTimeout(() => togglePanels('login'), 3500);
          } else {
            showAlert(data.message || 'Error al registrar.');
          }
        }).catch(() => {
          showAlert('Error de red. Verifica la conexión.');
          btn.disabled = false;
          btn.innerHTML = 'Validar y Registrarse';
        });
    }
  </script>
</body>
</html>
