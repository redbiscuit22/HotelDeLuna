<?php
session_start();
include('connection.php');

if(isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin WHERE username='$username'";
    $result = mysqli_query($connection, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        if(password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: admin.php');
            exit;
        } else {
            $error = "Invalid credentials";
        }
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5b67fa;
            --primary-dark: #4a54e0;
            --dark-color: #2d3748;
            --light-color: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 50%, #d4bafd 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background-attachment: fixed;
        }
        
        .login-container {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
        }
        
        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8a6df1 100%);
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-bottom: none;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(91, 103, 250, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8a6df1 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.875rem;
            border-radius: 8px;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #7b5ced 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(91, 103, 250, 0.3);
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .brand-logo {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            filter: brightness(0) invert(1);
        }
        
        .footer-text {
            color: rgba(6, 20, 107, 0.8);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="card-header">
                    <div class="d-flex justify-content-center align-items-center">
                        <h4 class="mb-0">ADMIN PORTAL</h4>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="text-center mb-4" style="color: var(--dark-color);">Sign in an Admin account</h5>
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="admin_login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-login">Continue</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-4">
                <small class="footer-text">© <?php echo date('Y'); ?> Hotel De Luna. All rights reserved.</small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>