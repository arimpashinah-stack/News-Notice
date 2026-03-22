<?php
// --- AIVEN CLOUD CONNECTION ---
$host = 'shina-news-arimpashinah-717b.i.aivencloud.com';
$port = '20110';
$dbname = 'defaultdb';
$user = 'avnadmin';
$password = getenv('AIVEN_PASSWORD') ?: 'AVNS_hj7G6NFLHYr7RQQiZOz';
$ssl_ca = 'src/ca.pem';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $password, [
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. AUTO-CREATE TABLES (Fixes the "Table doesn't exist" error)
    $pdo->exec("CREATE TABLE IF NOT EXISTS daily_news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        summary TEXT,
        article_url VARCHAR(255),
        fetch_date DATE,
        fetch_time INT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        email VARCHAR(100) UNIQUE,
        password_hash VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. FETCH FRESH NEWS FROM API (Ensures the cloud has data)
    $api_url = "https://hacker-news.firebaseio.com/v0/topstories.json";
    $top_stories = json_decode(file_get_contents($api_url));
    
    $pdo->exec("TRUNCATE TABLE daily_news");
    $insert = $pdo->prepare("INSERT INTO daily_news (title, summary, article_url, fetch_time, fetch_date) VALUES (?, ?, ?, ?, ?)");
    $current_time = time();
    $today = date('Y-m-d');

    for ($i = 0; $i < 5; $i++) {
        $story_id = $top_stories[$i];
        $story_data = json_decode(file_get_contents("https://hacker-news.firebaseio.com/v0/item/{$story_id}.json"), true);
        $url = $story_data['url'] ?? "https://news.ycombinator.com/item?id=".$story_id;
        $insert->execute([$story_data['title'], "Live tech update from Hacker News.", $url, $current_time, $today]);
    }

    // 3. GET DATA FOR THE DASHBOARD
    $news_items = $pdo->query("SELECT * FROM daily_news LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $registered_users = $pdo->query("SELECT id, username, email FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 4. GENERATE THE STATIC HTML
    ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shinan News - MENSA Project</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background: #f0f2f5; color: #333; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #0066cc; border-bottom: 3px solid #0066cc; padding-bottom: 10px; }
        .section { margin-top: 30px; }
        .news-box { border-left: 4px solid #0066cc; padding-left: 15px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .footer { margin-top: 50px; font-size: 0.85em; color: #777; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Shinan News Live Dashboard</h1>
        <p><i>Last Cloud Update: <?php echo date('Y-m-d H:i:s'); ?> (EAT)</i></p>

        <div class="section">
            <h2>🔥 Live Tech Feed</h2>
            <?php foreach($news_items as $news): ?>
                <div class="news-box">
                    <strong><a href="<?= $news['article_url'] ?>" target="_blank"><?= htmlspecialchars($news['title']) ?></a></strong>
                    <p><?= htmlspecialchars($news['summary']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section">
            <h2>👥 Registered Users (Cloud)</h2>
            <table>
                <tr><th>ID</th><th>Username</th><th>Email</th></tr>
                <?php foreach($registered_users as $u): ?>
                    <tr><td><?= $u['id'] ?></td><td><?= htmlspecialchars($u['username']) ?></td><td><?= htmlspecialchars($u['email']) ?></td></tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="footer">
            <h3>Project Group: MENSA</h3>
            <p><strong>Members:</strong> Arimpa Shinah, Nabagesera Mercy, Lwensisi Agnes Nassimbwa, Jamada Yasin Jamada, Nabbanja Barbra, Ebele Ben, Mukibi Samuel, Muyanja Isaac Robert, Maninga Kevin.</p>
        </div>
    </div>
</body>
</html>
<?php
    $content = ob_get_clean();
    file_put_contents('index.html', $content);
    echo "Successfully generated index.html with live news!";
} catch (Exception $e) { 
    echo "Build failed: " . $e->getMessage(); 
    exit(1); 
}
