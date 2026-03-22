<?php
// AIVEN CONNECTION (Use your credentials)
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

    // Fetch News and Users
    $news_items = $pdo->query("SELECT * FROM daily_news LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $registered_users = $pdo->query("SELECT id, username, email FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

    // START CAPTURING OUTPUT
    ob_start();
?>
<!DOCTYPE html>
<html>
<head><title>Shinan News - Live</title></head>
<body>
    <h1>Welcome to Shinan News</h1>
    <h2>Top Tech News</h2>
    <ul>
        <?php foreach($news_items as $n): ?>
            <li><a href="<?= $n['article_url'] ?>"><?= $n['title'] ?></a></li>
        <?php endforeach; ?>
    </ul>
    <h2>Registered Users</h2>
    <ul>
        <?php foreach($registered_users as $u): ?>
            <li><?= $u['username'] ?> (<?= $u['email'] ?>)</li>
        <?php endforeach; ?>
    </ul>
    <h2>Group Members</h2>
    <ul>
        <li>ARIMPA SHINAH (23/U/06403/PS) [cite: 1]</li>
        <li>NABAGESERA MERCY (23/U/0941) [cite: 1]</li>
        <li>LWENSISI AGNES NASSIMBWA (23/U/10892/PS) [cite: 1]</li>
        <li>JAMADA YASIN JAMADA (23/U/08420/EVE) [cite: 1]</li>
        <li>NABBANJA BARBRA (23/U12783//EVE) [cite: 1]</li>
        <li>EBELE BEN (23/U/2048) [cite: 1]</li>
        <li>MUKIBI SAMUEL (23/U/11833/PS) [cite: 1]</li>
        <li>MUYANJA ISAAC ROBERT (23/U/12421) [cite: 1]</li>
        <li>MANINGA KEVIN (23/U/11037/EVE) [cite: 1]</li>
    </ul>
</body>
</html>
<?php
    $html = ob_get_clean();
    file_put_contents('index.html', $html); // This creates the static file for GitHub Pages
    echo "Static site generated successfully!";
} catch (Exception $e) { echo $e->getMessage(); }
