<?php
session_start();
include '../includes/config.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] !== 'user') {
    header("Location: ../admin/admin_dashboard.php");
    exit();
}


$username = $_SESSION['username'];

try {
    $stmt = $db->query("SELECT * FROM rooms");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$question = "";
// Save question if submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['question'])) {
    $question = $_POST['question'];
    $user_id = $_SESSION['user_id']; // assuming user is logged in

    $stmt = $db->prepare("INSERT INTO questions (user_id, content) VALUES (?, ?)");
    $stmt->execute([$user_id, $question]);
}

// Fetch all submitted questions
$questions = $db->query("SELECT q.content, u.username FROM questions q JOIN users u ON q.user_id = u.id ORDER BY q.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

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
            align-items: flex-end
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
            margin-right: 10px;
            margin-left: 10px;
        }
        .topbar .profile {
            background: #4CAF50;
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
        .topbar .ask-btn {
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .topbar .ask-popup {
            display:none; 
            position:fixed; 
            top:30%; 
            left:40%; 
            background:#f2f2f2; 
            padding:20px; 
            border:1px solid #ccc;
        }
    </style>
</head>
<body>

<div class="topbar">

    <h2>Hello User, <?php echo htmlspecialchars($username); ?>!</h2>

    <!-- Profile Button -->
    <div class="container">

        <form method="POST" action="profile.php">   
              <button class="profile"type="submit">Profile</button>
        </form> 

        <form method="POST" action="../auth/logout.php">
           <button class="logout" type="submit">Logout</button>
        </form>

        <button class="ask-btn" onclick="showAskPopup()">Ask us anything!</button>
        <div id="askPopup" style="display:none; position:fixed; top:30%; left:40%; background:#f2f2f2; padding:20px; border:1px solid #ccc;">
            <form method="POST">
                <label>Ask us anything:</label><br>
                <input type="text" name="question" placeholder="Type your question here" style="width: 100%;"><br><br>
                <button type="submit">Submit</button>
                <button type="button" onclick="hideAskPopup()">Cancel</button>
            </form>
        </div>
        <script>
          function showAskPopup() {
              document.getElementById('askPopup').style.display = 'block';
            }
           function hideAskPopup() {
              document.getElementById('askPopup').style.display = 'none';
            }
        </script>
    </div>
</div>

<h2 style="text-align: center; margin-top: 30px;">Available Escape Rooms</h2>

<div class="container">
    <?php foreach ($rooms as $room): ?>
        <div class="card">
            <img src="../uploads/<?php echo htmlspecialchars($room['image']); ?>" alt="Escape Room">
            <h3><?php echo htmlspecialchars($room['name']); ?></h3>
            <p><?php echo htmlspecialchars($room['description']); ?></p>
            <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($room['difficulty']); ?></p>
            <p><strong>Price:</strong> <?php echo htmlspecialchars($room['price']); ?> SR</p>
            <a class="book-btn" href="escape_details.php?id=<?php echo $room['id']; ?>">Book Now</a>
        </div>
    <?php endforeach; ?>
</div>
<h3 style="text-align:center; margin-top: 40px;">Public Questions</h3>
<div style="margin: 0 auto; width: 80%;">
    <?php foreach ($questions as $q): ?>
        <div style="background:#fff; border-radius:8px; padding:15px; margin-bottom:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
            <strong><?php echo htmlspecialchars($q['username']); ?> asks:</strong><br>
            <div style="color: #333;"><?php echo $q['content']; ?></div> <!--  Intentionally not escaped for XSS -->
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>