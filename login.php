<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanInput($_POST['email']);
    $password = cleanInput($_POST['password']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            
            redirect('index.php');
        } else {
            $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
        }
    } catch(PDOException $e) {
        $error = "حدث خطأ في النظام: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة المصنع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-96">
        <div class="text-center mb-8">
            <i class="fas fa-industry text-6xl text-blue-600 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-800">نظام إدارة المصنع</h1>
            <p class="text-gray-600 mt-2">سجل الدخول إلى حسابك</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    <i class="fas fa-envelope ml-2"></i>البريد الإلكتروني
                </label>
                <input type="email" id="email" name="email" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="ادخل بريدك الإلكتروني">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    <i class="fas fa-lock ml-2"></i>كلمة المرور
                </label>
                <input type="password" id="password" name="password" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="ادخل كلمة المرور">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">
                <i class="fas fa-sign-in-alt ml-2"></i>تسجيل الدخول
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            <p>نسيت كلمة المرور؟ <a href="#" class="text-blue-600 hover:text-blue-800">إعادة تعيين</a></p>
        </div>
    </div>
</body>
</html>