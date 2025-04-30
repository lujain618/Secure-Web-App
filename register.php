<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = "";

// Connect to your database
$db = new PDO("sqlite:database.sqlite");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "❌ Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
    } else {
        try {
            $checkStmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $checkStmt->execute(['email' => $email]);
            $existingUser = $checkStmt->fetch();

            if ($existingUser) {
                $message = "❌ Email already registered. Please login or use a different email.";
            } else {
                $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashedPassword
                ]);

                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            background: linear-gradient(to right, #ffecd2, #fcb69f);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-box {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            width: 350px;
            text-align: center;
        }
        .register-box h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .register-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .register-box button {
            width: 100%;
            padding: 10px;
            background: #FF5722;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .register-box button:hover {
            background: #e64a19;
        }
        .register-box a {
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
    <div class="register-box">
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <a href="login.php">Already have an account? Login here</a>
        <div class="message"><?php echo $message; ?></div>
    </div>
</body>
</html>
