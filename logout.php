<?php
session_start();

// ❌ INSECURE: Not destroying the session — would keep the user logged in
// unset($_SESSION['user_id']); // only removes one variable, session still active

// ✅ SECURE: Destroy the full session and clear session data
session_destroy();

// ✅ Redirect to login page after logout
header("Location: login.php");
exit();
?>
