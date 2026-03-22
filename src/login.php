<?php
session_start();

// If already logged in, send straight to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$host = 'db';
$dbname = 'shinan_news_db';
$user = 'shinan_user';
$password = 'wsa26';
$message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = htmlspecialchars($_POST['email']);
        $submitted_password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify user exists and password is correct
        if ($user_data && password_verify($submitted_password, $user_data['password_hash'])) {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "<p style='color: red;'>❌ Invalid email or password.</p>";
        }
    }
} catch (PDOException $e) {
    die("Database connection failed.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Shinan News</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .container { max-width: 400px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="email"], input[type="password"] { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;}
        button:hover { background-color: #0052a3; }
        a { text-decoration: none; color: #0066cc; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Secure Login</h2>
        <?php echo $message; ?>
        <form method="POST" action="">
            <label>Email Address</label>
            <input type="email" name="email" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p><a href="index.php">← Back to Home</a></p>
    </div>
</body>
</html>
