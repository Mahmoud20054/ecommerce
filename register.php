<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['full_name']);
    $email   = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $phone   = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($password !== $confirm) {
        $error = 'كلمات المرور غير متطابقة';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } else {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'البريد الإلكتروني مسجل مسبقاً';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)');
            $insert->execute([$name, $email, $hashedPassword, $phone, $address, 'customer']);
            header('Location: login.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - NTPO       RDE</title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, sans-serif;
            background: linear-gradient(135deg, #0a2540 0%, #1a4a75 100%);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; padding: 2rem 1rem;
        }
        .reg-container {
            background: white; padding: 2.5rem; border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 480px;
        }
        .logo-area { text-align: center; margin-bottom: 1.5rem; }
        .logo-area h1 { color: var(--primary); font-size: 1.8rem; }
        .logo-area p { color: #64748b; font-size: 0.9rem; }
        h2 { color: var(--primary); text-align: center; margin-bottom: 1.5rem; font-size: 1.3rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; color: #475569; font-size: 0.9rem; font-weight: 500; }
        input, textarea {
            width: 100%; padding: 0.7rem 1rem; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: 0.95rem; transition: border-color 0.2s; font-family: inherit;
        }
        input:focus, textarea:focus { outline: none; border-color: var(--primary); }
        textarea { height: 70px; resize: none; }
        .btn {
            width: 100%; padding: 0.85rem; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-size: 1rem; cursor: pointer;
            font-weight: 600; margin-top: 0.5rem; transition: background 0.2s;
        }
        .btn:hover { background: #1a4a75; }
        .error {
            color: #dc3545; background: #fff5f5; border: 1px solid #fecaca;
            padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;
            font-size: 0.9rem; text-align: center;
        }
        .links { text-align: center; margin-top: 1.25rem; font-size: 0.9rem; color: #64748b; }
        .links a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
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
<div class="reg-container">
    <div class="logo-area">
           <a href="index.php" class="logo"><img src="uploads/logo.png" alt="NTPO 🛍️   🛍️      RDE" style="height: 85px; width: 125px; vertical-align: middle;"></a>
        <p>متجرك الإلكتروني الموثوق</p>
    </div>
    <h2>انضم إلينا اليوم</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <div class="form-group">
            <label>الاسم الكامل</label>
            <input type="text" name="full_name" placeholder="محمد أحمد" required>
        </div>
        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" placeholder="example@email.com" required>
        </div>
        <div class="form-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" placeholder="+970 5X XXX XXXX" required>
        </div>
        <div class="form-group">
            <label>عنوان التوصيل</label>
            <textarea name="address" placeholder="المدينة، الشارع، رقم المبنى..." required></textarea>
        </div>
        <div class="row">
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label>تأكيد كلمة المرور</label>
                <input type="password" name="confirm_password" placeholder="••••••••" required>
            </div>
        </div>
        <button type="submit" class="btn">إنشاء الحساب</button>
    </form>
    <div class="links">
        لديك حساب بالفعل؟ <a href="login.php">سجل دخولك هنا</a>
    </div>
</div>
</body>
</html>
