<?php
require_once 'config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE name LIKE ? LIMIT 6');
    $stmt->execute(['%' . $search . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM products LIMIT 6');
}
$products = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    $uid = $_SESSION['user_id'];
    $pid = (int)$_POST['product_id'];
    $qty = 1;
    $stmt = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
    $stmt->execute([$uid, $pid, $qty]);
    header('Location: cart.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NTPO       RDE</title>
    <style>
        :root { --primary: #3a0963; --accent: #f36f03; --bg: #4a85dd; --text: #4b1818; }
        body { font-family: system-ui, sans-serif; margin: 0; background: var(--bg); color: var(--text); }
        header { background: var(--primary); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.5rem; font-weight: bold; text-decoration: none; }
        nav a { color: white; text-decoration: none; margin-right: 1.5rem; font-size: 1rem; }
        nav a:hover { color: var(--accent); }
        .hero { background: linear-gradient(135deg, #0a2540 0%, #1a4a75 100%); color: white; text-align: center; padding: 5rem 1rem; }
        .hero h1 { font-size: 2.5rem; margin-bottom: 1rem; }
        .hero a { display: inline-block; background: var(--accent); color: var(--primary); padding: 0.75rem 2rem; border-radius: 5px; text-decoration: none; font-weight: bold; margin-top: 1rem; }
        .search-container { max-width: 500px; margin: -20px auto 3rem auto; padding: 0 1rem; }
        .search-box { display: flex; background: white; border-radius: 30px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .search-box input { flex: 1; border: none; padding: 1rem; outline: none; font-size: 1rem; }
        .search-box button { background: var(--primary); color: white; border: none; padding: 0 1.5rem; cursor: pointer; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .section-title { text-align: center; margin-bottom: 2rem; font-size: 2rem; color: var(--primary); }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; }
        .card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .card img { width: 100%; height: 220px; object-fit: cover; }
        .card-body { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .card-title { font-size: 1.2rem; margin: 0 0 0.5rem 0; color: var(--primary); }
        .price { font-weight: bold; color: #28a745; font-size: 1.1rem; margin-bottom: 1rem; }
        .actions { display: flex; gap: 0.5rem; }
        .btn-view { flex: 1; text-align: center; background: #e2e8f0; color: var(--primary); padding: 0.6rem; border-radius: 5px; text-decoration: none; font-size: 0.9rem; }
        .btn-add { flex: 1; background: var(--primary); color: white; border: none; padding: 0.6rem; border-radius: 5px; cursor: pointer; font-size: 0.9rem; }
   
   @media (max-width: 768px) {

    header {
        flex-direction: column;
        gap: 1rem;
    }

    nav {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    .container {
        padding: 1rem;
    }

    .grid {
        grid-template-columns: 1fr;
    }

    .sidebar {
        width: 100%;
        min-height: auto;
    }

    .main-content {
        padding: 1rem;
    }

    table {
        font-size: 0.8rem;
    }
}</style>
</head>
<body>

<header>
    <a href="index.php" class="logo"><img src="uploads/logo.png" alt="NTPO 🛍️   🛍️      RDE" style="height: 85px; width: 125px; vertical-align: middle;"></a>
    <nav>
        <a href="index.php">الرئيسية</a>
        <a href="products.php">المنتجات</a>
        <a href="cart.php">السلة</a>
        <a href="contact.php">اتصل بنا</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">حسابي</a>
            <a href="login.php?logout=1">خروج</a>
        <?php else: ?>
            <a href="login.php">تسجيل الدخول</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <h1>تسوق أحدث الأجهزة والإلكترونيات</h1>
    <p>عروض حصرية وجودة مضمونة - توصيل سريع وأسعار تنافسية</p>
    <a href="products.php">تسوق الآن</a>
</section>

<div class="search-container">
    <form action="products.php" method="GET" class="search-box">
        <input type="text" name="search" placeholder="ابحث عن منتج معين...">
        <button type="submit">بحث</button>
    </form>
</div>

<main class="container">
    <h2 class="section-title">منتجات مميزة</h2>
    <div class="grid">
        <?php foreach ($products as $product): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="">
                <div class="card-body">
                    <div>
                        <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="price"><?= htmlspecialchars($product['price']) ?> شيكل</div>
                    </div>
                    <div class="actions">
                        <a href="product-detail.php?id=<?= $product['product_id'] ?>" class="btn-view">التفاصيل</a>
                        <form action="index.php" method="POST" style="flex: 1; display: flex;">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" name="add_to_cart" class="btn-add">أضف للسلة</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>