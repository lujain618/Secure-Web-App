<?php
session_start();
$message = "";

// ✅ If user is already logged in, redirect them to home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

// ✅ SECURE: Connect to your database with error handling
try {
    $db = new PDO("sqlite:database.sqlite");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // ❌ INSECURE: vulnerable to SQL injection — DO NOT USE
        // $query = "SELECT * FROM users WHERE email = '$email'";
        // $result = $db->query($query);
        // $user = $result->fetch();

        // ✅ SECURE: Use a prepared statement to prevent SQL injection
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // ❌ INSECURE: plain-text password check — DO NOT USE
        // if ($user && $user['password'] == $password) {
        //     $_SESSION['user_id'] = $user['id'];
        //     $_SESSION['username'] = $user['username'];
        //     header("Location: home.php");
        //     exit();
        // }

        // ✅ SECURE: Use password_verify() to compare hashed password
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Optional: store role if using roles
            if (isset($user['role'])) {
                $_SESSION['role'] = $user['role'];
            }

            header("Location: home.php");
            exit();
        } else {
            $message = "❌ Invalid email or password.";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background: linear-gradient(to right, #a1c4fd, #c2e9fb);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            width: 320px;
            text-align: center;
        }
        .login-box h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .login-box button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .login-box button:hover {
            background: #45a049;
        }
        .login-box a {
            display: block;
            margin-top: 15px;
            color: #007BFF;
            text-decoration: none;
        }
        .message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form method="POST">
            <!-- ✅ SECURE: Frontend validation to require fields -->
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="register.php">Don't have an account? Register here</a>
        <div class="message"><?php echo $message; ?></div>
    </div>
</body>
</html>
