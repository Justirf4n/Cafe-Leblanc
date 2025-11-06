<?php
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'user';

    // Validation
    if (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if username or email already exists
        $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Username or email already exists';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $insert_query = "INSERT INTO users (username, email, password_hash, full_name, phone, is_active)
                            VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', TRUE)";

            if (mysqli_query($conn, $insert_query)) {
                $user_id = mysqli_insert_id($conn);

                // Insert address
                $address_query = "INSERT INTO addresses (user_id, address_line, is_default)
                                 VALUES ($user_id, '$address', TRUE)";
                mysqli_query($conn, $address_query);

                // If admin role selected, create admin record (FIXED - removed admin_level)
                if ($role === 'admin') {
                    $admin_query = "INSERT INTO admins (user_id, permissions)
                                   VALUES ($user_id, '{\"products\": true, \"orders\": true, \"users\": false, \"reports\": true, \"settings\": false}')";
                    mysqli_query($conn, $admin_query);
                }

                // Auto-login after registration
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                // Redirect based on role
                if ($role === 'admin') {
                    header('Location: admin_dashboard.php?registered=1');
                } else {
                    header('Location: index.php?registered=1');
                }
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cafe Leblanc</title>
    <link rel="stylesheet" href="styles/register.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-background"></div>
        <div class="auth-box">
            <div class="auth-header">
                <div class="auth-logo">CAFE LEBLANC</div>
                <h1>JOIN US</h1>
                <p>Create your account to start ordering</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="role">ACCOUNT TYPE *</label>
                    <select id="role" name="role" required>
                        <option value="user">USER - Customer Account</option>
                        <option value="admin">ADMIN - Management Account</option>
                    </select>
                    <p class="form-hint">Select User for ordering food, Admin for managing the system</p>
                </div>

                <div class="form-group">
                    <label for="username">USERNAME *</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">EMAIL *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="full_name">FULL NAME *</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="phone">PHONE NUMBER *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="address">DELIVERY ADDRESS *</label>
                    <textarea id="address" name="address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD * (min. 6 characters)</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">CONFIRM PASSWORD *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn-auth">REGISTER</button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="login.php">LOGIN HERE</a></p>
                <p><a href="index.php">← BACK TO CAFE</a></p>
            </div>
        </div>
    </div>
</body>
</html>