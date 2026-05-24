<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}


if (isset($_GET['mark_read'])) {
    $mid = (int)$_GET['mark_read'];
    $pdo->prepare('UPDATE contacts SET is_read = 1 WHERE message_id = ?')->execute([$mid]);
    header('Location: admin_messages.php');
    exit();
}


if (isset($_GET['delete'])) {
    $mid = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM contacts WHERE message_id = ?')->execute([$mid]);
    header('Location: admin_messages.php');
    exit();
}

$messages = $pdo->query('SELECT * FROM contacts ORDER BY message_id DESC')->fetchAll();
$unread = $pdo->query('SELECT COUNT(*) FROM contacts WHERE is_read = 0')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - الرسائل الواردة</title>
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
        .main-content { flex: 1; padding: 2rem; }
        .header { background: white; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { color: var(--primary); font-size: 1.5rem; }
        .table-box { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 0.75rem 1rem; text-align: right; color: var(--primary); border-bottom: 2px solid #e2e8f0; font-size: 0.88rem; }
        td { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.88rem; vertical-align: top; }
        tr.unread { background: #fffbeb; }
        tr:last-child td { border-bottom: none; }
        .msg-text { max-width: 280px; white-space: normal; word-break: break-word; color: #475569; }
        .read-badge { padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .read-badge.unread { background: #fee2e2; color: #991b1b; }
        .read-badge.read { background: #d1fae5; color: #065f46; }
        .btn-action { padding: 0.3rem 0.7rem; border-radius: 5px; font-size: 0.8rem; text-decoration: none; display: inline-block; margin-left: 0.3rem; }
        .btn-read { background: #dbeafe; color: #1e40af; }
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
    <a href="admin_users.php">👥 إدارة المستخدمين</a>
    <a href="admin_messages.php" class="active">
        ✉️ الرسائل الواردة
        <?php if ($unread > 0): ?>
            <span class="badge"><?= $unread ?></span>
        <?php endif; ?>
    </a>
    <a href="index.php" target="_blank">🌐 معاينة الموقع</a>
    <div class="sidebar-footer">
        <a href="login.php?logout=1" style="color:#f87171;">🚪 تسجيل الخروج</a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h1>✉️ رسائل اتصل بنا الواردة</h1>
        <span style="color:#64748b;font-size:0.9rem;"><?= $unread ?> رسالة غير مقروءة</span>
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المرسل</th>
                    <th>البريد الإلكتروني</th>
                    <th>الموضوع</th>
                    <th>نص الرسالة</th>
                    <th>وقت الإرسال</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr class="<?= !$msg['is_read'] ? 'unread' : '' ?>">
                        <td><strong>#<?= $msg['message_id'] ?></strong></td>
                        <td><?= htmlspecialchars($msg['name']) ?></td>
                        <td><?= htmlspecialchars($msg['email']) ?></td>
                        <td><strong><?= htmlspecialchars($msg['subject']) ?></strong></td>
                        <td class="msg-text"><?= htmlspecialchars($msg['message']) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($msg['submitted_at'])) ?></td>
                        <td>
                            <span class="read-badge <?= $msg['is_read'] ? 'read' : 'unread' ?>">
                                <?= $msg['is_read'] ? 'مقروءة' : 'جديدة' ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!$msg['is_read']): ?>
                                <a href="admin_messages.php?mark_read=<?= $msg['message_id'] ?>" class="btn-action btn-read">✔ تمييز كمقروء</a>
                            <?php endif; ?>
                            <a href="admin_messages.php?delete=<?= $msg['message_id'] ?>" class="btn-action btn-delete" onclick="return confirm('هل تريد حذف هذه الرسالة؟')">🗑 حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:2rem;">لا توجد رسائل</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
