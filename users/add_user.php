<?php
include '../header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $password = cleanInput($_POST['password']);
    $role = cleanInput($_POST['role']);
    $status = cleanInput($_POST['status']);

    // التحقق من البريد الإلكتروني
    if (!isValidEmail($email)) {
        $error = "البريد الإلكتروني غير صحيح";
    } else {
        try {
            // التحقق من عدم وجود البريد مسبقاً
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "البريد الإلكتروني مسجل مسبقاً";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $role, $status]);
                
                $success = "تم إضافة المستخدم بنجاح";
            }
        } catch(PDOException $e) {
            $error = "حدث خطأ في إضافة المستخدم: " . $e->getMessage();
        }
    }
}
?>

<div class="container mx-auto max-w-2xl">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">إضافة مستخدم جديد</h1>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="grid grid-cols-1 gap-6">
                <!-- الاسم والبريد -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الاسم الكامل *</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني *</label>
                        <input type="email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- كلمة المرور -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور *</label>
                    <input type="password" name="password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           minlength="6">
                    <p class="text-sm text-gray-500 mt-1">كلمة المرور يجب أن تكون 6 أحرف على الأقل</p>
                </div>

                <!-- الدور والحالة -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الدور *</label>
                        <select name="role" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="employee">موظف</option>
                            <option value="manager">مسؤول</option>
                            <option value="admin">مدير</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحالة *</label>
                        <select name="status" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active">نشط</option>
                            <option value="inactive">غير نشط</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- أزرار -->
            <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">
                    إلغاء
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    <i class="fas fa-save ml-2"></i>حفظ المستخدم
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>