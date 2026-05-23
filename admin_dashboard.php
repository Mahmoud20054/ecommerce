<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query('SELECT COUNT(*) FROM products');
$total_products = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM orders');
$total_orders = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "customer"');
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT SUM(total_amount) FROM orders WHERE status != "cancelled"');
$total_revenue = $stmt->fetchColumn() ?: 0;


$stmt = $pdo->query('SELECT COUNT(*) FROM contacts WHERE is_read = 0');
$unread_messages = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC LIMIT 5');
$recent_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - الرئيسية</title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; --bg: #f4f6f9; --sidebar: #1e293b; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: var(--bg); display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--sidebar); color: white; padding: 2rem 1rem; display: flex; flex-direction: column; }
        .sidebar-logo { text-align: center; margin-bottom: 2rem; }
        .sidebar-logo h2 { color: var(--accent); font-size: 1.4rem; }
        .sidebar-logo p { color: #94a3b8; font-size: 0.8rem; margin-top: 0.25rem; }
        .sidebar a { display: flex; align-items: center; gap: 0.6rem; color: #cbd5e1; padding: 0.75rem 1rem; text-decoration: none; border-radius: 8px; margin-bottom: 0.35rem; font-size: 0.95rem; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; }
        .sidebar .badge { background: #dc3545; color: white; font-size: 0.7rem; padding: 0.15rem 0.45rem; border-radius: 10px; margin-right: auto; }
        .sidebar-footer { margin-top: auto; padding-top: 1rem; border-top: 1px solid #334155; }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header h1 { color: var(--primary); font-size: 1.5rem; }
        .header-info { color: #64748b; font-size: 0.9rem; }
        .btn-logout { background: #dc3545; color: white; padding: 0.5rem 1.25rem; text-decoration: none; border-radius: 6px; font-size: 0.9rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-right: 5px solid var(--primary); position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.03; }
        .stat-card.orders { border-right-color: var(--accent); }
        .stat-card.revenue { border-right-color: #28a745; }
        .stat-card.messages { border-right-color: #dc3545; }
        .stat-card h3 { color: #64748b; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-card .number { font-size: 2.2rem; font-weight: 700; color: #1e293b; margin-top: 0.5rem; }
        .stat-card .label { color: #94a3b8; font-size: 0.8rem; margin-top: 0.25rem; }
        .data-table-box { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .data-table-box h2 { color: var(--primary); margin-bottom: 1.25rem; font-size: 1.2rem; display: flex; align-items: center; gap: 0.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 0.75rem 1rem; text-align: right; color: var(--primary); border-bottom: 2px solid #e2e8f0; font-size: 0.9rem; }
        td { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
        tr:last-child td { border-bottom: none; }
        .status-badge { padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-shipped { background: #dbeafe; color: #1e40af; }
        .status-delivered { background: #dcfce7; color: #14532d; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
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
        <h2>NTPO 🛍️   🛍️      RDE</h2>
        <p>لوحة تحكم الإدارة</p>
    </div>
    <a href="admin_dashboard.php" class="active">🏠 الرئيسية</a>
    <a href="admin_products.php">📦 إدارة المنتجات</a>
    <a href="admin_orders.php">🧾 إدارة الطلبات</a>
    <a href="admin_users.php">👥 إدارة المستخدمين</a>
    <a href="admin_messages.php">
        ✉️ الرسائل الواردة
        <?php if ($unread_messages > 0): ?>
            <span class="badge"><?= $unread_messages ?></span>
        <?php endif; ?>
    </a>
    <a href="index.php" target="_blank">🌐 معاينة الموقع</a>
    <div class="sidebar-footer">
        <a href="login.php?logout=1" style="color:#f87171;">🚪 تسجيل الخروج</a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h1>📊 لوحة التحكم الإحصائية</h1>
        <div style="display:flex;align-items:center;gap:1rem;">
            <span class="header-info">مرحباً، <?= htmlspecialchars($_SESSION['full_name']) ?></span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>إجمالي المنتجات</h3>
            <div class="number"><?= $total_products ?></div>
            <div class="label">منتج في المتجر</div>
        </div>
        <div class="stat-card orders">
            <h3>عدد الطلبات</h3>
            <div class="number"><?= $total_orders ?></div>
            <div class="label">طلب إجمالي</div>
        </div>
        <div class="stat-card">
            <h3>الزبائن المسجلين</h3>
            <div class="number"><?= $total_users ?></div>
            <div class="label">زبون نشط</div>
        </div>
        <div class="stat-card revenue">
            <h3>إجمالي الإيرادات</h3>
            <div class="number"><?= number_format($total_revenue, 0) ?></div>
            <div class="label">شيكل إسرائيلي</div>
        </div>
        <div class="stat-card messages">
            <h3>رسائل غير مقروءة</h3>
            <div class="number"><?= $unread_messages ?></div>
            <div class="label">رسالة جديدة</div>
        </div>
    </div>

    <div class="data-table-box">
        <h2>🕐 أحدث الطلبات الواردة</h2>
        <table>
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>اسم الزبون</th>
                    <th>التاريخ</th>
                    <th>المبلغ الإجمالي</th>
                    <th>حالة الطلب</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['order_id'] ?></strong></td>
                        <td><?= htmlspecialchars($order['full_name']) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                        <td><?= number_format($order['total_amount'], 2) ?> ₪</td>
                        <td>
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_orders)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem;">لا توجد طلبات حتى الآن</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
