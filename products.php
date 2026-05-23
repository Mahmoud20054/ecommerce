<?php
require_once 'config.php';

$cat_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$categories = $pdo->query('SELECT * FROM categories')->fetchAll();
if (!empty($type)) {
    $query .= ' AND device_type = ?';
    $params[] = $type;
                    }  
$query = 'SELECT * FROM products WHERE 1=1';
$params = [];

if (!empty($search)) {
    $query .= ' AND name LIKE ?';
    $params[] = '%' . $search . '%';
}

if ($cat_id > 0) {
    $query .= ' AND category_id = ?';
    $params[] = $cat_id;
}

if ($sort === 'low_high') {
    $query .= ' ORDER BY price ASC';
} elseif ($sort === 'high_low') {
    $query .= ' ORDER BY price DESC';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    $uid = $_SESSION['user_id'];
    $pid = (int)$_POST['product_id'];
    $stmt = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
    $stmt->execute([$uid, $pid]);
    header('Location: cart.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كل المنتجات</title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; --bg: #f4f6f9; }
        body { font-family: system-ui, sans-serif; margin: 0; background: var(--bg); }
        header { background: var(--primary); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.5rem; font-weight: bold; text-decoration: none; }
        nav a { color: white; text-decoration: none; margin-right: 1.5rem; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .filter-bar { background: white; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .filter-bar select, .filter-bar input { padding: 0.5rem 1rem; border: 1px solid #ddd; border-radius: 5px; outline: none; }
        .filter-bar button { background: var(--primary); color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 5px; cursor: pointer; }
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
                                    }
    </style>
</head>
<body>

<header>
   <a href="index.php" class="logo"><img src="images/logo.png" alt="EliteShop" style="height: 40px; vertical-align: middle;"></a>
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

<main class="container">
    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="بحث بالاسم...">
        <select name="category">
            <option value="0">جميع الفئات</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= $cat_id === (int)$cat['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="type">

    <option value="">كل الأجهزة</option>

    <option value="هاتف">هواتف</option>

    <option value="تابلت">تابلت</option>

    <option value="لابتوب">لابتوب</option>

</select>
        <select name="sort">
            <option value="">ترتيب حسب</option>
            <option value="low_high" <?= $sort === 'low_high' ? 'selected' : '' ?>>السعر: من الأقل للأعلى</option>
            <option value="high_low" <?= $sort === 'high_low' ? 'selected' : '' ?>>السعر: من الأعلى للأقل</option>
        </select>
        <button type="submit">تطبيق</button>
    </form>

    <div class="grid">
        <?php foreach ($products as $product): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="">
                <div class="card-body">
                    <div>
                        <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="price"><?= htmlspecialchars($product['price']) ?> شيكل</div>
                    </div>
                    <p>النوع: <?= htmlspecialchars($product['device_type']) ?></p>
                    <div class="actions">
                        <a href="product-detail.php?id=<?= $product['product_id'] ?>" class="btn-view">التفاصيل</a>
                        <form action="products.php" method="POST" style="flex: 1; display: flex;">
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