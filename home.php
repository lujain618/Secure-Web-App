<?php
session_start();

// ❌ INSECURE: Allowing access without session check — DO NOT USE
// if (true) { echo "Hello Hacker"; }

// ✅ SECURE: Block unauthenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ SECURE: Connect to the database with error handling
try {
    $db = new PDO("sqlite:database.sqlite");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$username = $_SESSION['username'];

try {
    // ❌ INSECURE: Running raw queries without error handling
    // $stmt = $db->query("SELECT * FROM rooms");
    // $rooms = $stmt->fetchAll();

    // ✅ SECURE: Use try-catch for robust database access
    $stmt = $db->query("SELECT * FROM rooms");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Escape Rooms</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: linear-gradient(to right, #8EC5FC, #E0C3FC);
        }
        .topbar {
            background-color: #ffffffcc;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar h2 {
            margin: 0;
            color: #333;
        }
        .topbar .logout {
            background: #f44336;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
        }
        .card {
            background: white;
            border-radius: 10px;
            margin: 15px;
            width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .card h3 {
            margin: 15px 0 5px;
            color: #333;
        }
        .card p {
            margin: 0 15px 15px;
            color: #666;
        }
        .book-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .book-btn:hover {
            background: #388e3c;
        }
    </style>
</head>
<body>

<!-- ✅ Secure: escape username output -->
<div class="topbar">
    <h2>Hello User, <?php echo htmlspecialchars($username); ?>!</h2>
    <form method="POST" action="logout.php">
        <button class="logout" type="submit">Logout</button>
    </form>
</div>

<h2 style="text-align: center; margin-top: 30px;">Available Escape Rooms</h2>

<div class="container">
    <?php foreach ($rooms as $room): ?>
        <div class="card">
            <!-- ✅ Secure: escape all database output to prevent XSS -->
            <img src="images/<?php echo htmlspecialchars($room['image']); ?>" alt="Escape Room">
            <h3><?php echo htmlspecialchars($room['name']); ?></h3>
            <p><?php echo htmlspecialchars($room['description']); ?></p>
            <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($room['difficulty']); ?></p>
            <p><strong>Price:</strong> <?php echo htmlspecialchars($room['price']); ?> SR</p>
            <a class="book-btn" href="escape_details.php?id=<?php echo $room['id']; ?>">Book Now</a>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
