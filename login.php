<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | PRS Application</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background-color: #f4f6f9; /* Light gray like your dashboard */
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .card {
      background-color: #fff; /* Clean white card */
      border: 1px solid #dee2e6;
      padding: 2rem;
      border-radius: 1rem;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    h4 {
      color: #343a40;
      text-align: center;
      font-weight: bold;
    }

    .form-control {
      background-color: #fff;
      border: 1px solid #ced4da;
      color: #495057;
    }

    .form-control::placeholder {
      color: #adb5bd;
    }

    .btn-login {
      background-color: #343a40;
      color: #fff;
      font-weight: bold;
      border: none;
    }

    .btn-login:hover {
      background-color: #FFD700; /* Gold on hover */
      color: #343a40;
    }

    .text-muted {
      color: #6c757d !important;
    }
  </style>
</head>
<body>

 <!-- Login Form -->
<div class="card">
  <div class="text-center mb-4">
    <h4>PRS Application</h4>
    <small class="text-muted">Please sign in to continue</small>
  </div>
  <form id="loginForm" method="POST">
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
    </div>
    <div class="d-grid">
      <button type="submit" class="btn btn-gold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Login
      </button>
    </div>
  </form>
  <div id="loginError" style="display: none;" class="alert alert-danger mt-3"></div>
</div>

<!-- jQuery (add this BEFORE your login.js) -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Your login.js -->
<script src="dist/js/login.js"></script>

</body>
</html>
