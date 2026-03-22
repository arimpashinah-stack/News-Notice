<?php
session_start();

// 1. SECURE THE PAGE
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- AIVEN CLOUD CONNECTION ---
$host = 'shina-news-arimpashinah-717b.i.aivencloud.com';
$port = '20110';
$dbname = 'defaultdb';
$user = 'avnadmin';
$password = 'AVNS_hj7G6NFLHYr7RQQiZOz';
$ssl_ca = __DIR__ . '/ca.pem';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", 
        $user, 
        $password,
        [
            PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    // Auto-create tables if they don't exist on Aiven yet
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE, email VARCHAR(100) UNIQUE, password_hash VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS daily_news (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), summary TEXT, article_url VARCHAR(255), fetch_date DATE, fetch_time INT)");

    // 2. THE 5-HOUR NEWS LOGIC
    $current_time = time();
    $stmt = $pdo->query("SELECT MAX(fetch_time) as last_fetch FROM daily_news");
    $row = $stmt->fetch();
    $last_fetch = $row['last_fetch'] ?: 0;

    if (($current_time - $last_fetch) >= 18000) {
        $api_url = "https://hacker-news.firebaseio.com/v0/topstories.json";
        $top_stories = json_decode(file_get_contents($api_url));
        $pdo->exec("TRUNCATE TABLE daily_news");
        $today = date('Y-m-d');
        $insert = $pdo->prepare("INSERT INTO daily_news (title, summary, article_url, fetch_time, fetch_date) VALUES (?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < 5; $i++) {
            $story = json_decode(file_get_contents("https://hacker-news.firebaseio.com/v0/item/{$top_stories[$i]}.json"), true);
            $url = $story['url'] ?? "https://news.ycombinator.com/item?id=".$top_stories[$i];
            $insert->execute([$story['title'], "Live tech update from Hacker News.", $url, $current_time, $today]);
        }
        $last_fetch = $current_time;
    }

    $next_release = $last_fetch + 18000;
    $news_items = $pdo->query("SELECT * FROM daily_news LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $registered_users = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Cloud Connection Failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Shinan News</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f0f2f5; color: #1c1e21; }
        .container { max-width: 1100px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #0066cc; color: white; padding: 15px 25px; border-radius: 8px; margin-bottom: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 25px; }
        h2 { border-left: 5px solid #0066cc; padding-left: 15px; color: #0066cc; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #666; font-weight: 600; }
        .timer-box { background: #fff3cd; color: #856404; padding: 12px; border-radius: 6px; font-weight: bold; border: 1px solid #ffeeba; }
        .btn-logout { background: #ffffff; color: #cc0000; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

        <div class="card">
            <h2>Live Cloud News Feed</h2>
            <div class="timer-box">Next Release in: <span id="countdown">Calculating...</span></div>
            <br>
            <?php foreach ($news_items as $item): ?>
                <div style="margin-bottom: 15px;">
                    <h4 style="margin: 0;"><a href="<?php echo $item['article_url']; ?>" target="_blank"><?php echo htmlspecialchars($item['title']); ?></a></h4>
                    <p style="color: #666; margin: 5px 0; font-size: 0.9em;"><?php echo $item['summary']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2>Registered Users (Cloud)</h2>
            <table>
                <tr><th>ID</th><th>Username</th><th>Email</th><th>Registration Date</th></tr>
                <?php foreach ($registered_users as $usr): ?>
                <tr><td><?php echo $usr['id']; ?></td><td><?php echo $usr['username']; ?></td><td><?php echo $usr['email']; ?></td><td><?php echo $usr['created_at']; ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="card">
            <h2>Group Members - MENSA Lab</h2>
            <table>
                <tr><th>Name</th><th>Registration Number</th><th>Student Number</th></tr>
                <tr><td>ARIMPA SHINAH</td><td>23/U/06403/PS</td><td>2300706403</td></tr>
                <tr><td>NABAGESERA MERCY</td><td>23/U/0941</td><td>2300700941</td></tr>
                <tr><td>LWENSISI AGNES NASSIMBWA</td><td>23/U/10892/PS</td><td>2300710892</td></tr>
                <tr><td>JAMADA YASIN JAMADA</td><td>23/U/08420/EVE</td><td>2300708420</td></tr>
                <tr><td>NABBANJA BARBRA</td><td>23/U12783//EVE</td><td>2300712783</td></tr>
                <tr><td>EBELE BEN</td><td>23/U/2048</td><td>230072048</td></tr>
                <tr><td>MUKIBI SAMUEL</td><td>23/U/11833/PS</td><td>2300711833</td></tr>
                <tr><td>MUYANJA ISAAC ROBERT</td><td>23/U/12421</td><td>2300712421</td></tr>
                <tr><td>MANINGA KEVIN</td><td>23/U/11037/EVE</td><td>2300711037</td></tr>
            </table>
        </div>
    </div>

    <script>
        var nextRel = <?php echo $next_release; ?> * 1000;
        setInterval(function() {
            var dist = nextRel - new Date().getTime();
            if (dist < 0) { location.reload(); }
            var h = Math.floor((dist % (86400000)) / 3600000);
            var m = Math.floor((dist % 3600000) / 60000);
            var s = Math.floor((dist % 60000) / 1000);
            document.getElementById("countdown").innerHTML = h + "h " + m + "m " + s + "s";
        }, 1000);
    </script>
</body>
</html>
