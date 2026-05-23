<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (!empty($name)) {
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?');
        $stmt->execute([$name, $phone, $address, $user_id]);
        $_SESSION['full_name'] = $name;
        $success = 'تم تحديث بياناتك الشخصية بنجاح.';
    }
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$orderStmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC');
$orderStmt->execute([$user_id]);
$orders = $orderStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي</title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; --bg: #f4f6f9; }
        body { font-family: system-ui, sans-serif; margin: 0; background: var(--bg); }
        header { background: var(--primary); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.5rem; font-weight: bold; text-decoration: none; }
        nav a { color: white; text-decoration: none; margin-right: 1.5rem; }
        .container { max-width: 1100px; margin: 3rem auto; padding: 2rem; display: flex; gap: 2rem; flex-wrap: wrap; }
        .profile-form { flex: 1; min-width: 300px; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); height: fit-content; }
        .orders-history { flex: 2; min-width: 300px; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: var(--primary); margin-top: 0; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input, textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-update { background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer; width: 100%; }
        .success-box { background: #d4edda; color: #155724; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 0.75rem; text-align: right; border-bottom: 2px solid #e2e8f0; }
        td { padding: 0.75rem; border-bottom: 1px solid #e2e8f0; }
        .status-badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
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
        <a href="login.php?logout=1">خروج</a>
    </nav>
</header>

<main class="container">
    <div class="profile-form">
        <h2>بيانات حسابي</h2>
        <?php if (!empty($success)): ?>
            <div class="success-box"><?= $success ?></div>
        <?php endif; ?>
        <form action="profile.php" method="POST">
            <div class="form-group">
                <label>الاسم الكامل</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#e9ecef;">
            </div>
            <div class="form-group">
                <label>رقم الهاتف</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <div class="form-group">
                <label>عنوان التوصيل الحالي</label>
                <textarea name="address"><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <button type="submit" class="btn-update">حفظ التغييرات</button>
        </form>
    </div>

    <div class="orders-history">
        <h2>أرشيف طلباتي السابقة</h2>
        <?php if (empty($orders)): ?>
            <p>لا توجد لديك أي طلبات مسجلة حتى الآن.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>التاريخ</th>
                        <th>إجمالي السعر</th>
                        <th>حالة الطلب</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['order_id'] ?></td>
                            <td><?= date('Y-m-d', strtotime($order['order_date'])) ?></td>
                            <td><?= $order['total_amount'] ?> شيكل</td>
                            <td>
                                <span class="status-badge">
                                    <?= $order['status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

</body>
</html>