<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    $uid = $_SESSION['user_id'];
    $pid = (int)$_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    $stmt = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?');
    $stmt->execute([$uid, $pid, $qty, $qty]);
    header('Location: cart.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; --bg: #f4f6f9; }
        body { font-family: system-ui, sans-serif; margin: 0; background: var(--bg); }
        header { background: var(--primary); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.5rem; font-weight: bold; text-decoration: none; }
        nav a { color: white; text-decoration: none; margin-right: 1.5rem; }
        .container { max-width: 1000px; margin: 4rem auto; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); display: flex; gap: 3rem; flex-wrap: wrap; }
        .product-image { flex: 1; min-width: 300px; }
        .product-image img { width: 100%; border-radius: 8px; object-fit: cover; }
        .product-info { flex: 1; min-width: 300px; display: flex; flex-direction: column; justify-content: center; }
        .product-info h1 { color: var(--primary); margin-top: 0; font-size: 2rem; }
        .price { font-size: 1.5rem; color: #28a745; font-weight: bold; margin: 1rem 0; }
        .desc { color: #666; line-height: 1.6; margin-bottom: 2rem; }
        .stock { margin-bottom: 1rem; font-size: 0.9rem; color: #888; }
        .form-inline { display: flex; gap: 1rem; align-items: center; }
        .form-inline input { width: 70px; padding: 0.6rem; border: 1px solid #ddd; border-radius: 5px; text-align: center; font-size: 1rem; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 0.75rem 2rem; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        .back-link { display: inline-block; margin-top: 1.5rem; color: var(--primary); text-decoration: none; font-weight: bold; }
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
    <div class="product-image">
        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="">
    </div>
    <div class="product-info">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <div class="price"><?= htmlspecialchars($product['price']) ?> شيكل</div>
        <p>النوع: <?= htmlspecialchars($product['device_type']) ?></p>
        <p class="desc"><?= htmlspecialchars($product['description']) ?></p>
        <div class="stock">الكمية المتاحة في المخزن: <?= $product['stock_quantity'] ?></div>
        
        <form action="product-detail.php?id=<?= $product['product_id'] ?>" method="POST" class="form-inline">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
            <button type="submit" name="add_to_cart" class="btn-submit">إضافة إلى السلة</button>
        </form>
        
        <div>
            <a href="products.php" class="back-link">← العودة للمنتجات</a>
        </div>
    </div>
</main>

</body>
</html>