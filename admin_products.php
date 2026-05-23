<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$edit_product = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $edit_product = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name']);
    $category_id    = (int)$_POST['category_id'];
    $price          = (float)$_POST['price'];
    $description    = trim($_POST['description']);
    $stock_quantity = (int)$_POST['stock_quantity'];
    $image_url      = '';

    if (!empty($_FILES['product_image']['name'])) {
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
        $file_name = $_FILES['product_image']['name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $image_name = time() . '_' . $file_name;
            $target     = 'uploads/' . $image_name;
            move_uploaded_file($_FILES['product_image']['tmp_name'], $target);
            $image_url = $target;
        }
    }

   
    if (empty($image_url) && !empty($_POST['old_image_url'])) {
        $image_url = $_POST['old_image_url'];
    }

  
    $stmt_cat   = $pdo->prepare('SELECT name FROM categories WHERE category_id = ?');
    $stmt_cat->execute([$category_id]);
    $cat_row    = $stmt_cat->fetch();
    $device_type = $cat_row ? $cat_row['name'] : '';

    if (isset($_POST['save_product'])) {
        if (!empty($_POST['product_id'])) {
          
            $stmt = $pdo->prepare('UPDATE products SET name=?, category_id=?, price=?, description=?, stock_quantity=?, image_url=? WHERE product_id=?');
            $stmt->execute([
                $name,
                $category_id,
                $price,
                $description,
                $stock_quantity,
                $image_url,
                $device_type,
                (int)$_POST['product_id']
            ]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (name, category_id, price, description, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $name,
                $category_id,
                $price,
                $description,
                $stock_quantity,
                $image_url,
                
            ]);
        }
        header('Location: admin_products.php');
        exit();
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM products WHERE product_id = ?');
    $stmt->execute([(int)$_GET['id']]);
    header('Location: admin_products.php');
    exit();
}

$products   = $pdo->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - إدارة المنتجات</title>
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
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header h1 { color: var(--primary); font-size: 1.5rem; }
        .content-grid { display: flex; gap: 2rem; flex-wrap: wrap; }
        .form-box { flex: 1; min-width: 300px; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: fit-content; }
        .form-box h2 { color: var(--primary); margin-bottom: 1.25rem; font-size: 1.15rem; }
        .table-box { flex: 2; min-width: 400px; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table-box h2 { color: var(--primary); margin-bottom: 1.25rem; font-size: 1.15rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; color: #475569; font-size: 0.88rem; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 0.65rem 0.85rem; border: 1.5px solid #e2e8f0; border-radius: 7px; font-size: 0.9rem; font-family: inherit; transition: border-color 0.2s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--primary); }
        textarea { height: 80px; resize: none; }
        .btn-save { background: var(--primary); color: white; border: none; padding: 0.75rem; width: 100%; border-radius: 7px; cursor: pointer; font-size: 0.95rem; font-weight: 600; transition: background 0.2s; }
        .btn-save:hover { background: #1a4a75; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 0.75rem 0.85rem; text-align: right; color: var(--primary); border-bottom: 2px solid #e2e8f0; font-size: 0.85rem; }
        td { padding: 0.75rem 0.85rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 0.85rem; }
        tr:last-child td { border-bottom: none; }
        .prod-img { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; }
        .btn-edit { color: #0284c7; text-decoration: none; margin-left: 0.75rem; font-size: 0.82rem; }
        .btn-delete { color: #dc3545; text-decoration: none; font-size: 0.82rem; }
        .cancel-link { display: block; text-align: center; margin-top: 0.75rem; color: #64748b; text-decoration: none; font-size: 0.9rem; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; min-height: auto; }
            .main-content { padding: 1rem; }
            table { font-size: 0.8rem; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <h2>NTPO 🛍️   🛍️      RDE</h2>
        <p>لوحة تحكم الإدارة</p>
    </div>
    <a href="admin_dashboard.php">🏠 الرئيسية</a>
    <a href="admin_products.php" class="active">📦 إدارة المنتجات</a>
    <a href="admin_orders.php">🧾 إدارة الطلبات</a>
    <a href="admin_users.php">👥 إدارة المستخدمين</a>
    <a href="admin_messages.php">✉️ الرسائل الواردة</a>
    <a href="index.php" target="_blank">🌐 معاينة الموقع</a>
    <div class="sidebar-footer">
        <a href="login.php?logout=1" style="color:#f87171;">🚪 تسجيل الخروج</a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h1>📦 إدارة قائمة المنتجات</h1>
    </div>

    <div class="content-grid">
        <div class="form-box">
            <h2><?= $edit_product ? '✏️ تعديل منتج' : '➕ إضافة منتج جديد' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= $edit_product ? $edit_product['product_id'] : '' ?>">
                <input type="hidden" name="old_image_url" value="<?= $edit_product ? htmlspecialchars($edit_product['image_url']) : '' ?>">

                <div class="form-group">
                    <label>اسم المنتج</label>
                    <input type="text" name="name" value="<?= $edit_product ? htmlspecialchars($edit_product['name']) : '' ?>" required>
                </div>

                
                <div class="form-group">
                    <label>نوع الجهاز (الفئة)</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"
                                <?= $edit_product && $edit_product['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>السعر (₪)</label>
                    <input type="number" step="0.01" min="0" name="price" value="<?= $edit_product ? $edit_product['price'] : '' ?>" required>
                </div>

                <div class="form-group">
                    <label>الكمية في المخزن</label>
                    <input type="number" min="0" name="stock_quantity" value="<?= $edit_product ? $edit_product['stock_quantity'] : '' ?>" required>
                </div>

                
                <div class="form-group">
                    <label>صورة المنتج</label>
                    <input type="file" name="product_image" accept="image/*">
                    <?php if ($edit_product && $edit_product['image_url']): ?>
                        <small style="color:#64748b;">الصورة الحالية: <?= htmlspecialchars($edit_product['image_url']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>الوصف</label>
                    <textarea name="description" required><?= $edit_product ? htmlspecialchars($edit_product['description']) : '' ?></textarea>
                </div>

                <button type="submit" name="save_product" class="btn-save">💾 حفظ المنتج</button>
                <?php if ($edit_product): ?>
                    <a href="admin_products.php" class="cancel-link">إلغاء التعديل</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-box">
            <h2>📋 المنتجات الحالية (<?= count($products) ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>الاسم</th>
                        <th>نوع الجهاز</th>
                        <th>السعر</th>
                        <th>المخزن</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($p['image_url']) ?>" class="prod-img" alt=""></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            
                            <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
                            <td><?= number_format($p['price'], 2) ?> ₪</td>
                            <td><?= $p['stock_quantity'] ?></td>
                            <td>
                                <a href="admin_products.php?action=edit&id=<?= $p['product_id'] ?>" class="btn-edit">✏️ تعديل</a>
                                <a href="admin_products.php?action=delete&id=<?= $p['product_id'] ?>" class="btn-delete"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">🗑 حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:2rem;">لا توجد منتجات</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>