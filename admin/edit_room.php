<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../auth/login.php");
    exit();
}

$room_id = (int)$_GET['id'];
$upload_dir = 'images/';

// Fetch room details
$stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    $_SESSION['message'] = "Room not found";
    header("Location: admin_dashboard.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $difficulty = trim($_POST['difficulty']);
    $price = (float)$_POST['price'];
    
    $image = $room['image']; // Keep existing image unless new one is uploaded
    
    // Handle file upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        // Delete old image if exists
         if (!empty($room['image']) && file_exists($upload_dir . $room['image'])) {
          unlink($upload_dir . $room['image']);
        }

        // Upload new image
        $image_name = uniqid('room_', true) . '.' . $file_ext;
        $target_file = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = $image_name;
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }

    if (empty($message)) {
        try {
            $stmt = $db->prepare("UPDATE rooms SET name = ?, description = ?, difficulty = ?, price = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $description, $difficulty, $price, $image, $room_id]);
            
            $_SESSION['message'] = "Room updated successfully!";
            header("Location: admin_dashboard.php");
            exit();
        } catch (PDOException $e) {
            $message = "Error updating room: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Room</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #4CAF50;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            background: #f2dede;
            color: #a94442;
        }
        .current-image {
            max-width: 100%;  /* Ensures image scales down to fit the container */
            height: auto;     /* Maintains aspect ratio */
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <h2>Edit Escape Room</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="section">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Room Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($room['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($room['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="difficulty">Difficulty</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="Easy" <?php echo $room['difficulty'] == 'Easy' ? 'selected' : ''; ?>>Easy</option>
                        <option value="Medium" <?php echo $room['difficulty'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="Hard" <?php echo $room['difficulty'] == 'Hard' ? 'selected' : ''; ?>>Hard</option>
                        <option value="Expert" <?php echo $room['difficulty'] == 'Expert' ? 'selected' : ''; ?>>Expert</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Price (SR)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($room['price']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="image">New Room Image (Leave blank to keep current)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if (!empty($room['image'])): ?>
                        <div>
                            <p>Current Image:</p>
                            <img src="../images/<?php echo htmlspecialchars($room['image']); ?>" class="current-image">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Room</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
