<?php
include '../header.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    redirect('users.php');
}

// جلب بيانات المستخدم
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('users.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $role = cleanInput($_POST['role']);
    $status = cleanInput($_POST['status']);
    $password = cleanInput($_POST['password']);

    try {
        if (!empty($password)) {
            // تحديث مع كلمة المرور
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, status=?, password=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$name, $email, $role, $status, $hashed_password, $id]);
        } else {
            // تحديث بدون كلمة المرور
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, status=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$name, $email, $role, $status, $id]);
        }
        
        $success = "تم تحديث المستخدم بنجاح";
        // تحديث بيانات المستخدم بعد التعديل
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    } catch(PDOException $e) {
        $error = "حدث خطأ في تحديث المستخدم: " . $e->getMessage();
    }
}
?>

<div class="container mx-auto max-w-2xl">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">تعديل المستخدم</h1>

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
                        <input type="text" name="name" value="<?php echo $user['name']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني *</label>
                        <input type="email" name="email" value="<?php echo $user['email']; ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- كلمة المرور -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور الجديدة</label>
                    <input type="password" name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           minlength="6" placeholder="اتركه فارغاً إذا لم ترد التغيير">
                    <p class="text-sm text-gray-500 mt-1">كلمة المرور يجب أن تكون 6 أحرف على الأقل</p>
                </div>

                <!-- الدور والحالة -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الدور *</label>
                        <select name="role" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="employee" <?php echo $user['role'] == 'employee' ? 'selected' : ''; ?>>موظف</option>
                            <option value="manager" <?php echo $user['role'] == 'manager' ? 'selected' : ''; ?>>مسؤول</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>مدير</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحالة *</label>
                        <select name="status" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>نشط</option>
                            <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                        </select>
                    </div>
                </div>

                <!-- معلومات إضافية -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium text-gray-800 mb-2">معلومات إضافية</h3>
                    <p class="text-sm text-gray-600">تاريخ التسجيل: <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></p>
                    <?php if ($user['updated_at'] != $user['created_at']): ?>
                    <p class="text-sm text-gray-600">آخر تحديث: <?php echo date('Y-m-d H:i', strtotime($user['updated_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- أزرار -->
            <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">
                    إلغاء
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    <i class="fas fa-save ml-2"></i>تحديث المستخدم
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>