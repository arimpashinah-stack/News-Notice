<?php
$host = 'db';
$dbname = 'shinan_news_db';
$user = 'shinan_user';
$password = 'wsa26';
$message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        // Securely hash the password before storing it
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        
        try {
            $stmt->execute([$username, $email, $hashed_password]);
            $message = "<p style='color: green;'>✅ User registered successfully! <a href='dashboard.php'>Go to Dashboard</a></p>";
        } catch (PDOException $e) {
            $message = "<p style='color: red;'>❌ Registration failed: Username or Email might already exist.</p>";
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Shinan News</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .container { max-width: 400px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="text"], input[type="email"], input[type="password"] { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0052a3; }
        a { text-decoration: none; color: #0066cc; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create an Account</h2>
        <?php echo $message; ?>
        <form method="POST" action="">
            <label>Username</label>
            <input type="text" name="username" required>
            
            <label>Email</label>
            <input type="email" name="email" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit">Register</button>
        </form>
        <p><a href="index.php">← Back to Home</a></p>
    </div>
</body>
</html>
