<?php
// Database connection variables
$host = 'db'; // This matches the service name in docker-compose.yml
$dbname = 'shinan_news_db';
$user = 'shinan_user';
$password = 'wsa26'; // Your custom password

try {
    // Attempt to connect using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection_status = "<p style='color: green;'>✅ Successfully connected to the Shinan News database!</p>";
} catch (PDOException $e) {
    $connection_status = "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shinan Tech News</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        a { text-decoration: none; color: #0066cc; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Shinan Tech News</h1>
        
        <?php echo $connection_status; ?>
        
        <p>Get your top 5 daily tech updates.</p>
        
        <hr>
        <p>
            <a href="register.php">Create an Account</a> | 
            <a href="dashboard.php">Go to Dashboard</a>
        </p>
    </div>
</body>
</html>
