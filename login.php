<?php
session_start();
include 'connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];

        // Redirect to book.php if it was saved
        if (isset($_SESSION['redirect_to'])) {
            $redirect = $_SESSION['redirect_to'];
            unset($_SESSION['redirect_to']);
            header("Location: $redirect");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | Hotel De Luna</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary-color: #0095ff;
            --primary-hover: #007ad6;
            --input-border: #ced4da;
            --text-color: #333;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            background: url('images/home-slide-2.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            margin: 0;
            padding: 0;
        }

        .login-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            backdrop-filter: blur(6px);
            background-color: rgba(255, 255, 255, 0.1);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .login-card h3 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--text-color);
            font-family: 'Pinyon Script', cursive;
            font-size: 2.2rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group-text {
            background-color: transparent;
            border-color: var(--input-border);
        }

        .btn-custom {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 10px;
            border-radius: 8px;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: var(--primary-hover);
        }

        #togglePassword {
            cursor: pointer;
            background-color: transparent;
            border-left: none;
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.3);
            color: var(--error-color);
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border-color: rgba(40, 167, 69, 0.3);
            color: var(--success-color);
        }

        .text-center small {
            font-size: 0.9rem;
        }

        /* Mobile-specific styles */
        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
                backdrop-filter: blur(4px);
            }
            
            .login-card {
                padding: 25px;
                border-radius: 12px;
            }
            
            .login-card h3 {
                font-size: 1.8rem;
                margin-bottom: 15px;
            }
            
            .form-control, .btn-custom {
                padding: 8px 12px;
                font-size: 0.95rem;
            }
            
            .input-group-text, #togglePassword {
                padding: 8px 12px;
            }
        }

        @media (max-width: 400px) {
            .login-card {
                padding: 20px;
            }
            
            .login-card h3 {
                font-size: 1.6rem;
            }
            
            .form-label {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <h3>Welcome to Hotel De Luna</h3>

            <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
                <div class="alert alert-success">Registration successful! Please login.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" class="input-group-text" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-custom mb-3">Login</button>
            </form>

            <div class="mt-3 text-center">
                <small>Don't have an account? <a href="register.php">Register here</a></small>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Better mobile viewport handling
        function setViewportHeight() {
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        // Initialize
        setViewportHeight();
        window.addEventListener('resize', setViewportHeight);
    </script>
</body>

</html>