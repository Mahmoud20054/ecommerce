<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
    $stmt->execute([$status, $order_id]);
    header('Location: admin_orders.php');
    exit();
}


$view_order = null;
$order_items = [];
if (isset($_GET['view'])) {
    $oid = (int)$_GET['view'];
    $stmt = $pdo->prepare('SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?');
    $stmt->execute([$oid]);
    $view_order = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?');
    $stmt->execute([$oid]);
    $order_items = $stmt->fetchAll();
}

$orders = $pdo->query('SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - إدارة الطلبات</title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; --bg: #f4f6f9; --sidebar: #1e293b; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: var(--bg); display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--sidebar); color: white; padding: 2rem 1rem; display: flex; flex-direction: column; }
        .sidebar-logo { text-align: center; margin-bottom: 2rem; }
        .sidebar-logo h2 { color: var(--accent); font-size: 1.4rem; }
        .sidebar-logo p { color: #94a3b8; font-size: 0.8rem; }
        .sidebar a { display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; padding: 0.75rem 1rem; text-decoration: none; border-radius: 8px; margin-bottom: 0.35rem; font-size: 0.95rem; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .sidebar-footer { margin-top: auto; padding-top: 1rem; border-top: 1px solid #334155; }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .header { background: white; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .header h1 { color: var(--primary); font-size: 1.5rem; }
        .table-box { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 0.75rem 1rem; text-align: right; color: var(--primary); border-bottom: 2px solid #e2e8f0; font-size: 0.88rem; }
        td { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.88rem; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        select { padding: 0.4rem 0.7rem; border: 1px solid #e2e8f0; border-radius: 6px; background: white; font-size: 0.85rem; }
        .btn-update { background: var(--primary); color: white; border: none; padding: 0.4rem 0.9rem; border-radius: 5px; cursor: pointer; font-size: 0.82rem; }
        .btn-view { background: #dbeafe; color: #1e40af; padding: 0.3rem 0.7rem; border-radius: 5px; font-size: 0.82rem; text-decoration: none; display: inline-block; }
        .status-badge { padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-shipped { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #dcfce7; color: #14532d; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        .detail-box { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-right: 5px solid var(--accent); }
        .detail-box h2 { color: var(--primary); margin-bottom: 1rem; font-size: 1.2rem; }
        .detail-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px; }
        .detail-meta div { font-size: 0.9rem; }
        .detail-meta strong { color: var(--primary); display: block; margin-bottom: 0.2rem; }
        .back-btn { display: inline-block; margin-bottom: 1rem; color: var(--primary); text-decoration: none; font-size: 0.9rem; }
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

<div class="sidebar">
    <div class="sidebar-logo">
  <a href="index.php" class="logo"><img src="uploads/logo.png" alt="NTPO 🛍️   🛍️      RDE"style="height: 100px; width: 250px; vertical-align: middle;"></a>
        <p>لوحة تحكم الإدارة</p>
    </div>
    <a href="admin_dashboard.php">🏠 الرئيسية</a>
    <a href="admin_products.php">📦 إدارة المنتجات</a>
    <a href="admin_orders.php" class="active">🧾 إدارة الطلبات</a>
    <a href="admin_users.php">👥 إدارة المستخدمين</a>
    <a href="admin_messages.php">✉️ الرسائل الواردة</a>
    <a href="index.php" target="_blank">🌐 معاينة الموقع</a>
    <div class="sidebar-footer">
        <a href="login.php?logout=1" style="color:#f87171;">🚪 تسجيل الخروج</a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h1>🧾 إدارة طلبات الزبائن</h1>
    </div>

    <?php if ($view_order): ?>
        
        <a href="admin_orders.php" class="back-btn">← العودة لقائمة الطلبات</a>
        <div class="detail-box">
            <h2>📋 تفاصيل الطلب #<?= $view_order['order_id'] ?></h2>
            <div class="detail-meta">
                <div><strong>اسم الزبون</strong><?= htmlspecialchars($view_order['full_name']) ?></div>
                <div><strong>البريد الإلكتروني</strong><?= htmlspecialchars($view_order['email']) ?></div>
                <div><strong>تاريخ الطلب</strong><?= date('Y-m-d H:i', strtotime($view_order['order_date'])) ?></div>
                <div><strong>حالة الطلب</strong>
                    <span class="status-badge status-<?= $view_order['status'] ?>"><?= $view_order['status'] ?></span>
                </div>
                <div><strong>عنوان التوصيل</strong><?= htmlspecialchars($view_order['shipping_address']) ?></div>
                <div><strong>المبلغ الإجمالي</strong><?= number_format($view_order['total_amount'], 2) ?> ₪</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>اسم المنتج</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $oi): ?>
                        <tr>
                            <td><?= htmlspecialchars($oi['name']) ?></td>
                            <td><?= $oi['quantity'] ?></td>
                            <td><?= number_format($oi['unit_price'], 2) ?> ₪</td>
                            <td><?= number_format($oi['unit_price'] * $oi['quantity'], 2) ?> ₪</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        
        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>الزبون</th>
                        <th>التاريخ</th>
                        <th>المبلغ</th>
                        <th>العنوان</th>
                        <th>الحالة</th>
                        <th>تعديل الحالة</th>
                        <th>التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?= $order['order_id'] ?></strong></td>
                            <td><?= htmlspecialchars($order['full_name']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> ₪</td>
                            <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($order['shipping_address']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form action="admin_orders.php" method="POST" style="display:flex;align-items:center;gap:0.4rem;">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="status">
                                        <option value="pending"   <?= $order['status'] === 'pending'   ? 'selected' : '' ?>>pending</option>
                                        <option value="paid"      <?= $order['status'] === 'paid'      ? 'selected' : '' ?>>paid</option>
                                        <option value="shipped"   <?= $order['status'] === 'shipped'   ? 'selected' : '' ?>>shipped</option>
                                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>delivered</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">حفظ</button>
                                </form>
                            </td>
                            <td>
                                <a href="admin_orders.php?view=<?= $order['order_id'] ?>" class="btn-view">📋 عرض</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:2rem;">لا توجد طلبات</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
