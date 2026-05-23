<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


if (isset($_GET['remove'])) {
    $cid = (int)$_GET['remove'];
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ? AND user_id = ?');
    $stmt->execute([$cid, $user_id]);
    header('Location: cart.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $cid = (int)$_POST['cart_id'];
    $qty = max(1, (int)$_POST['quantity']);
    $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND user_id = ?');
    $stmt->execute([$qty, $cid, $user_id]);
    header('Location: cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $userStmt = $pdo->prepare('SELECT address FROM users WHERE user_id = ?');
    $userStmt->execute([$user_id]);
    $u = $userStmt->fetch();

    $cartStmt = $pdo->prepare(
        'SELECT c.quantity, p.product_id, p.price, p.stock_quantity
         FROM cart_items c JOIN products p ON c.product_id = p.product_id
         WHERE c.user_id = ?'
    );
    $cartStmt->execute([$user_id]);
    $items = $cartStmt->fetchAll();

    if (!empty($items)) {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $pdo->beginTransaction();
        try {
            $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)');
            $orderStmt->execute([$user_id, $total, $u['address']]);
            $order_id = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            $stockStmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?');

            foreach ($items as $item) {
                $itemStmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                $stockStmt->execute([$item['quantity'], $item['product_id']]);
            }

            $clearCart = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
            $clearCart->execute([$user_id]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
    header('Location: profile.php');
    exit();
}

$stmt = $pdo->prepare(
    'SELECT c.cart_id, c.quantity, p.name, p.price, p.image_url, p.product_id
     FROM cart_items c JOIN products p ON c.product_id = p.product_id
     WHERE c.user_id = ?'
);
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سلة المشتريات -NTPO       RDE</title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; --bg: #f4f6f9; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: var(--bg); }
        header { background: var(--primary); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.4rem; font-weight: bold; text-decoration: none; }
        nav a { color: white; text-decoration: none; margin-right: 1.5rem; font-size: 0.95rem; }
        nav a:hover { color: var(--accent); }
        footer { background: var(--primary); color: #94a3b8; text-align: center; padding: 1.5rem; margin-top: 4rem; font-size: 0.9rem; }
        .container { max-width: 1000px; margin: 3rem auto; padding: 0 1.5rem; }
        h1 { color: var(--primary); margin-bottom: 2rem; font-size: 1.8rem; }
        .cart-box { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 1rem; text-align: right; color: var(--primary); border-bottom: 2px solid #e2e8f0; font-size: 0.9rem; }
        td { padding: 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        .product-td { display: flex; align-items: center; gap: 1rem; }
        .product-td img { width: 65px; height: 65px; object-fit: cover; border-radius: 8px; }
        .product-td span { font-weight: 500; color: var(--primary); }
        .qty-input { width: 65px; padding: 0.4rem; text-align: center; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; }
        .btn-remove { background: #fee2e2; color: #991b1b; border: none; padding: 0.4rem 0.8rem; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 0.82rem; display: inline-block; }
        .btn-remove:hover { background: #fecaca; }
        .summary-box { background: white; border-radius: 12px; padding: 1.5rem 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; }
        .summary-box .total-label { color: #64748b; font-size: 1rem; }
        .summary-box .total-amount { font-size: 1.8rem; font-weight: 700; color: #28a745; }
        .checkout-actions { display: flex; justify-content: flex-end; margin-top: 1.5rem; }
        .btn-checkout { background: #28a745; color: white; border: none; padding: 0.85rem 2.5rem; border-radius: 8px; font-size: 1.05rem; cursor: pointer; font-weight: 600; transition: background 0.2s; }
        .btn-checkout:hover { background: #218838; }
        .empty-cart { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
        .empty-cart .icon { font-size: 4rem; margin-bottom: 1rem; }
        .empty-cart p { font-size: 1.1rem; margin-bottom: 1.5rem; }
        .empty-cart a { background: var(--primary); color: white; padding: 0.75rem 2rem; border-radius: 8px; text-decoration: none; font-weight: 600; }
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
        <a href="profile.php">حسابي</a>
        <a href="login.php?logout=1">خروج</a>
    </nav>
</header>

<main class="container">
    <h1>🛒 سلة المشتريات</h1>

    <?php if (empty($items)): ?>
        <div class="cart-box">
            <div class="empty-cart">
                <div class="icon">🛒</div>
                <p>سلتك فارغة حالياً</p>
                <a href="products.php">تصفح المنتجات</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-box">
            <table>
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>السعر</th>
                        <th>الكمية</th>
                        <th>الإجمالي</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="product-td">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="">
                                    <span><?= htmlspecialchars($item['name']) ?></span>
                                </div>
                            </td>
                            <td><?= number_format($item['price'], 2) ?> ₪</td>
                            <td>
                                <form action="cart.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input" onchange="this.form.submit()">
                                    <input type="hidden" name="update_qty" value="1">
                                </form>
                            </td>
                            <td><strong><?= number_format($item['price'] * $item['quantity'], 2) ?> ₪</strong></td>
                            <td>
                                <a href="cart.php?remove=<?= $item['cart_id'] ?>" class="btn-remove" onclick="return confirm('هل تريد حذف هذا المنتج من السلة؟')">🗑 حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="summary-box">
            <div>
                <div class="total-label">المجموع الكلي</div>
                <div class="total-amount"><?= number_format($total, 2) ?> ₪</div>
            </div>
            <form action="cart.php" method="POST">
                <button type="submit" name="checkout" class="btn-checkout">✅ إتمام الشراء</button>
            </form>
        </div>
    <?php endif; ?>
</main>

<footer>
    <p>© <?= date('Y') ?>Mahmoud el hisee</p>
</footer>

</body>
</html>
