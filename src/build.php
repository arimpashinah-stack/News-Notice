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

    // 1. AUTO-CREATE TABLES
    $pdo->exec("CREATE TABLE IF NOT EXISTS daily_news (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), summary TEXT, article_url VARCHAR(255), fetch_date DATE, fetch_time INT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE, email VARCHAR(100) UNIQUE, password_hash VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

    // 2. FETCH NEWS FROM API
    $api_url = "https://hacker-news.firebaseio.com/v0/topstories.json";
    $top_stories = json_decode(file_get_contents($api_url));
    $pdo->exec("TRUNCATE TABLE daily_news");
    $insert = $pdo->prepare("INSERT INTO daily_news (title, summary, article_url, fetch_time, fetch_date) VALUES (?, ?, ?, ?, ?)");
    $current_time = time();
    $today = date('Y-m-d');

    for ($i = 0; $i < 5; $i++) {
        $story = json_decode(file_get_contents("https://hacker-news.firebaseio.com/v0/item/{$top_stories[$i]}.json"), true);
        $url = $story['url'] ?? "https://news.ycombinator.com/item?id=".$top_stories[$i];
        $insert->execute([$story['title'], "Live tech update from Hacker News.", $url, $current_time, $today]);
    }

    // 3. GET DATA (Removed the Users query)
    $news_items = $pdo->query("SELECT * FROM daily_news LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // 4. GENERATE THE HTML
    ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shinan News - Group R Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background: #f0f2f5; color: #333; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #0066cc; border-bottom: 3px solid #0066cc; padding-bottom: 10px; margin-top: 0; }
        .timer-badge { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; font-weight: bold; border: 1px solid #ffeeba; display: inline-block; margin: 20px 0; font-size: 1.1em; }
        .section { margin-top: 30px; }
        
        /* Updated News Item Styles */
        .news-item { border-left: 4px solid #0066cc; padding: 10px 20px 15px 20px; margin-bottom: 25px; background: #fdfdfd; border-radius: 0 8px 8px 0; }
        .news-item h3 { margin: 0 0 5px 0; color: #222; }
        .read-more { display: inline-block; margin-top: 8px; color: white; background-color: #0066cc; padding: 6px 15px; text-decoration: none; border-radius: 5px; font-size: 0.9em; font-weight: bold; transition: background 0.3s; }
        .read-more:hover { background-color: #0052a3; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 30px; }
        th, td { padding: 14px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; color: #555; text-transform: uppercase; font-size: 0.85em; letter-spacing: 1px; }
        .group-header { background: #0066cc; color: white; padding: 10px 15px; border-radius: 6px 6px 0 0; margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Shinan News Live Dashboard</h1>
        <p><i>System Status: Operational | Last Build: <?php echo date('Y-m-d H:i:s'); ?> (EAT)</i></p>

        <div class="timer-badge">
            Next News Cycle in: <span id="countdown">Calculating...</span>
        </div>

        <div class="section">
            <h2>🔥 Live Tech Feed</h2>
            <?php foreach($news_items as $news): ?>
                <div class="news-item">
                    <h3><?= htmlspecialchars($news['title']) ?></h3>
                    <p style="color: #666; margin: 5px 0 10px 0;"><?= htmlspecialchars($news['summary']) ?></p>
                    <a href="<?= $news['article_url'] ?>" target="_blank" class="read-more">Read full article ➔</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section">
            [cite_start]<h2 class="group-header">🎓 Project Group: R [cite: 1]</h2>
            <table>
                <tr><th>NAME</th><th>REGISTRATION NUMBER</th><th>STUDENT NUMBER</th></tr>
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
        function updateTimer() {
            var now = new Date();
            var utcHour = now.getUTCHours();
            
            // GitHub Action runs every 5 hours (0, 5, 10, 15, 20 UTC)
            var nextIntervals = [0, 5, 10, 15, 20, 24];
            var nextHour = nextIntervals.find(h => h > utcHour);
            
            var targetDate = new Date(now);
            targetDate.setUTCHours(nextHour, 0, 0, 0);
            
            var diff = targetDate - now;
            
            var hours = Math.floor(diff / 3600000);
            var minutes = Math.floor((diff % 3600000) / 60000);
            var seconds = Math.floor((diff % 60000) / 1000);
            
            document.getElementById("countdown").innerHTML = hours + "h " + minutes + "m " + seconds + "s";
        }
        setInterval(updateTimer, 1000);
        updateTimer();
    </script>
</body>
</html>
<?php
    $content = ob_get_clean();
    file_put_contents('index.html', $content);
    echo "Successfully generated Group R dashboard with clickable links!";
} catch (Exception $e) { echo "Build failed: " . $e->getMessage(); exit(1); }
?>
