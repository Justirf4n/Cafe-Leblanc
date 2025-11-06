<?php
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Check if user is admin from database
    $user_id = $_SESSION['user_id'];
    $admin_check = "SELECT a.admin_id FROM admins a WHERE a.user_id = $user_id";
    $admin_result = mysqli_query($conn, $admin_check);
    
    if (mysqli_num_rows($admin_result) > 0) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT u.*, a.admin_id
              FROM users u
              LEFT JOIN admins a ON u.user_id = a.user_id
              WHERE (u.username = '$username' OR u.email = '$username') AND u.is_active = TRUE";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password_hash'])) {
            // Update last login
            mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE user_id = {$user['user_id']}");
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['admin_id'] ? 'admin' : 'user';
            
            // Redirect based on role
            if ($user['admin_id']) {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $error = 'Invalid credentials';
        }
    } else {
        $error = 'User not found';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafe Leblanc</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-background"></div>
        
        <div class="auth-box">
            <div class="auth-header">
                <div class="auth-logo">CAFE LEBLANC</div>
                <h1>WELCOME BACK</h1>
                <p>Enter your credentials to access your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Registration successful! You can now login.</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-success">Logout successful.</div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="username">USERNAME / EMAIL</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-auth">LOGIN</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">REGISTER HERE</a></p>
                <p><a href="index.php">← BACK TO CAFE</a></p>
            </div>
        </div>
    </div>
</body>
</html>