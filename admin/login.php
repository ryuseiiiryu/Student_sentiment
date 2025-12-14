<?php
// admin/login.php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>EduSense Admin Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../frontend/style.css">
</head>
<body class="auth-bg">

  <main class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card auth-card shadow-sm">
      <div class="card-body">

        <h4 class="mt-2 text-center">Admin Sign In</h4>
        <p class="small text-muted text-center">Login to view the feedback dashboard</p>

        <form method="POST" action="auth.php">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" class="form-control" required placeholder="admin">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required placeholder="••••••">
          </div>

          <button class="btn btn-primary w-100" type="submit">Sign In</button>

          <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger mt-3 mb-0">Invalid credentials.</div>
          <?php endif; ?>
        </form>

      </div>
    </div>
  </main>

</body>
</html>
