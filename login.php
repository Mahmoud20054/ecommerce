<?php
require_once 'config.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $error = 'البريد الإلكتروني أو كلمة المرور خاطئة';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - NTPO       RDE</title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, sans-serif;
            background: linear-gradient(135deg, #0a2540 0%, #1a4a75 100%);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background: white; padding: 2.5rem; border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 420px;
        }
        .logo-area { text-align: center; margin-bottom: 2rem; }
        .logo-area h1 { color: var(--primary); font-size: 2rem; }
        .logo-area p { color: #64748b; font-size: 0.9rem; margin-top: 0.25rem; }
        h2 { color: var(--primary); text-align: center; margin-bottom: 1.75rem; font-size: 1.4rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: 0.5rem; color: #475569; font-size: 0.9rem; font-weight: 500; }
        input {
            width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: 1rem; transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: var(--primary); }
        .btn {
            width: 100%; padding: 0.85rem; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-size: 1rem; cursor: pointer;
            font-weight: 600; transition: background 0.2s;
        }
        .btn:hover { background: #1a4a75; }
        .error {
            color: #dc3545; background: #fff5f5; border: 1px solid #fecaca;
            padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem;
            font-size: 0.9rem; text-align: center;
        }
        .links { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #64748b; }
        .links a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .links a:hover { text-decoration: underline; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }
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
    <a href="index.php" class="logo"><img src="uploads/logo.png" alt="NTPO 🛍️   🛍️      RDE" style="height: 85px; width: 125px; vertical-align: middle;"></a>
  
</header>
<div class="login-container">
    <div class="logo-area">
        <h1>NTPO          RDE</h1>
        <p>متجرك الإلكتروني الموثوق</p>
    </div>
    <h2>مرحباً بك مجدداً</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" placeholder="example@email.com" required>
        </div>
        <div class="form-group">
            <label>كلمة المرور</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn">تسجيل الدخول</button>
    </form>
    <hr class="divider">
    <div class="links">
        ليس لديك حساب؟ <a href="register.php">سجل حساباً جديداً</a>
    </div>
</div>
</body>
</html>
