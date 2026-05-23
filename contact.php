<?php
require_once 'config.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($message)) {
        $stmt = $pdo->prepare('INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $subject, $message]);
        $success = 'تم إرسال رسالتك بنجاح! سنرد عليك في أقرب وقت ممكن.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتصل بنا - NTPO       RDE</title>
    <style>
        :root { --primary: #0a2540; --accent: #ffb000; --bg: #f4f6f9; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: var(--bg); }
        header { background: var(--primary); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.4rem; font-weight: bold; text-decoration: none; }
        nav a { color: white; text-decoration: none; margin-right: 1.5rem; font-size: 0.95rem; }
        nav a:hover { color: var(--accent); }
        footer { background: var(--primary); color: #94a3b8; text-align: center; padding: 1.5rem; margin-top: 4rem; font-size: 0.9rem; }
        .container { max-width: 1000px; margin: 3rem auto; padding: 0 1.5rem; display: flex; gap: 2rem; flex-wrap: wrap; }
        .contact-form-box { flex: 1.5; min-width: 300px; background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .contact-info-box { flex: 1; min-width: 250px; background: var(--primary); color: white; padding: 2.5rem; border-radius: 12px; display: flex; flex-direction: column; gap: 1.5rem; }
        h2 { color: var(--primary); margin-bottom: 1.5rem; font-size: 1.4rem; }
        .contact-info-box h2 { color: white; }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 0.5rem; color: #475569; font-size: 0.9rem; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; font-family: inherit; transition: border-color 0.2s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--primary); }
        textarea { height: 130px; resize: none; }
        .btn-send { background: var(--primary); color: white; border: none; padding: 0.85rem; border-radius: 8px; cursor: pointer; width: 100%; font-size: 1rem; font-weight: 600; transition: background 0.2s; }
        .btn-send:hover { background: #1a4a75; }
        .success-msg { background: #d1fae5; color: #065f46; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; font-size: 0.95rem; }
        .info-item { display: flex; align-items: flex-start; gap: 0.75rem; }
        .info-item .icon { font-size: 1.3rem; margin-top: 0.1rem; }
        .info-item div { font-size: 0.95rem; line-height: 1.6; }
        .info-item strong { display: block; color: var(--accent); margin-bottom: 0.15rem; }
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
    <a href="index.php" class="logo">NTPO 🛍️   🛍️      RDE </a>
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
    <div class="contact-form-box">
        <h2>📩 أرسل لنا رسالة</h2>
        <?php if (!empty($success)): ?>
            <div class="success-msg">✅ <?= $success ?></div>
        <?php endif; ?>
        <form action="contact.php" method="POST">
            <div class="form-group">
                <label>الاسم الكامل</label>
                <input type="text" name="name" placeholder="محمد أحمد" required>
            </div>
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" placeholder="example@email.com" required>
            </div>
            <div class="form-group">
                <label>الموضوع</label>
                <select name="subject">
                    <option value="استفسار">استفسار عام</option>
                    <option value="شكوى">تقديم شكوى</option>
                    <option value="اقتراح">اقتراح لتطوير الموقع</option>
                </select>
            </div>
            <div class="form-group">
                <label>نص الرسالة</label>
                <textarea name="message" placeholder="اكتب رسالتك هنا..." required></textarea>
            </div>
            <button type="submit" class="btn-send">📨 إرسال الرسالة</button>
        </form>
    </div>

    <div class="contact-info-box">
        <h2>📞 معلومات التواصل</h2>
        <div class="info-item">
            <div class="icon">📍</div>
            <div><strong>العنوان</strong>فلسطين، غزة</div>
        </div>
        <div class="info-item">
            <div class="icon">📱</div>
            <div><strong>رقم الهاتف</strong>+970 597 564 929</div>
        </div>
        <div class="info-item">
            <div class="icon">📧</div>
            <div><strong>البريد الإلكتروني</strong>melhisee@gmail.com</div>
        </div>
        <div class="info-item">
            <div class="icon">🕐</div>
            <div><strong>ساعات العمل</strong>السبت – الخميس: 9 صباحاً – 9 مساءً</div>
        </div>
    </div>
</main>

<footer>
    <p>© <?= date('Y') ?> Mahmoud el hisee جميع الحقوق محفوظة</p>
</footer>

</body>
</html>
