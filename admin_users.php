<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if (isset($_GET['toggle_role'])) {
    $uid = (int)$_GET['toggle_role'];
    $current_role = $_GET['current'];
    $new_role = $current_role === 'admin' ? 'customer' : 'admin';

    if ($uid !== (int)$_SESSION['user_id']) {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE user_id = ?');
        $stmt->execute([$new_role, $uid]);
        $success = 'تم تغيير صلاحية المستخدم بنجاح.';
    }
}


if (isset($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    if ($uid !== (int)$_SESSION['user_id']) {
        
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
        $stmt->execute([$uid]);
        $has_orders = $stmt->fetchColumn();

        if ($has_orders > 0) {
            $error = 'لا يمكن حذف هذا المستخدم لأن لديه طلبات مسجلة في النظام.';
        } else {
            $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$uid]);
            $success = 'تم حذف المستخدم بنجاح.';
        }
    }
}

$users = $pdo->query('SELECT user_id, full_name, email, phone, address, role FROM users ORDER BY role ASC, user_id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - إدارة الحسابات</title>
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
        .main-content { flex: 1; padding: 2rem; }
        .header { background: white; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .header h1 { color: var(--primary); font-size: 1.5rem; }
        .alert { padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.9rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .table-box { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 0.75rem 1rem; text-align: right; color: var(--primary); border-bottom: 2px solid #e2e8f0; font-size: 0.88rem; }
        td { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.88rem; }
        tr:last-child td { border-bottom: none; }
        .role-badge { padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .role-badge.admin { background: #fef3c7; color: #92400e; }
        .role-badge.customer { background: #dbeafe; color: #1e40af; }
        .btn-action { padding: 0.3rem 0.7rem; border-radius: 5px; font-size: 0.8rem; text-decoration: none; display: inline-block; margin-left: 0.3rem; }
        .btn-toggle { background: #ede9fe; color: #4f46e5; }
        .btn-delete { background: #fee2e2; color: #991b1b; }
        .btn-action:hover { opacity: 0.8; }
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
       <a href="index.php" class="logo"><img src="uploads/logo.png" alt="NTPO 🛍️   🛍️      RDE" style="height: 100px; width: 250px; vertical-align: middle;"></a>
        <p>لوحة تحكم الإدارة</p>
    </div>
    <a href="admin_dashboard.php">🏠 الرئيسية</a>
    <a href="admin_products.php">📦 إدارة المنتجات</a>
    <a href="admin_orders.php">🧾 إدارة الطلبات</a>
    <a href="admin_users.php" class="active">👥 إدارة المستخدمين</a>
    <a href="admin_messages.php">✉️ الرسائل الواردة</a>
    <a href="index.php" target="_blank">🌐 معاينة الموقع</a>
    <div class="sidebar-footer">
        <a href="login.php?logout=1" style="color:#f87171;">🚪 تسجيل الخروج</a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h1>👥 إدارة حسابات النظام</h1>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم الكامل</th>
                    <th>البريد الإلكتروني</th>
                    <th>رقم الهاتف</th>
                    <th>العنوان</th>
                    <th>الصلاحية</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong>#<?= $u['user_id'] ?></strong></td>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($u['address'] ?? '-') ?></td>
                        <td>
                            <span class="role-badge <?= $u['role'] ?>">
                                <?= $u['role'] === 'admin' ? '👑 أدمن' : '🛒 زبون' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['user_id'] !== $_SESSION['user_id']): ?>
                                <a href="admin_users.php?toggle_role=<?= $u['user_id'] ?>&current=<?= $u['role'] ?>"
                                   class="btn-action btn-toggle"
                                   onclick="return confirm('هل تريد تغيير صلاحية هذا المستخدم؟')">🔄 تغيير الصلاحية</a>
                                <a href="admin_users.php?delete=<?= $u['user_id'] ?>"
                                   class="btn-action btn-delete"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">🗑 حذف</a>
                            <?php else: ?>
                                <span style="color:#94a3b8;font-size:0.85rem;">حسابك الحالي</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
