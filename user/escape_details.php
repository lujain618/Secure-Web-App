<?php
session_start();
include '../includes/config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


if (!isset($_GET['id'])) {
    die("Room ID not provided.");
}


$id = (int) $_GET['id'];
$stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    die("Room not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($room['name']); ?> - Details</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #dbe6f6, #c5796d);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
        }
        img {
            width: 100%;
            border-radius: 10px;
            max-height: 400px;
            object-fit: cover;
        }
        h2 {
            margin-top: 20px;
        }
        form {
            margin-top: 20px;
        }
        select, button {
            padding: 10px;
            font-size: 16px;
            margin: 10px;
            border-radius: 6px;
            border: 1px solid #aaa;
        }
        button {
            background: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: #388e3c;
        }
    </style>
</head>
<body>

<div class="container">
    <img src="../uploads/<?php echo htmlspecialchars($room['image']); ?>" alt="Room image">
    <h2><?php echo htmlspecialchars($room['name']); ?></h2>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($room['description']); ?></p>
    <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($room['difficulty']); ?></p>
    <p><strong>Price:</strong> <?php echo htmlspecialchars($room['price']); ?> SR</p>

    <form method="post" action="cart.php">
        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">

        <label for="day">Choose Day:</label>
        <select name="day" required>
            <option value="">-- Select a day --</option>
            <option>Sunday</option>
            <option>Monday</option>
            <option>Tuesday</option>
            <option>Wednesday</option>
            <option>Thursday</option>
        </select>

        <label for="time">Choose Time:</label>
        <select name="time" required>
            <option value="">-- Select a time --</option>
            <option>3:00 PM</option>
            <option>5:00 PM</option>
            <option>7:00 PM</option>
        </select>

        <button type="submit">Add to Cart</button>
    </form>
</div>

</body>
</html> 