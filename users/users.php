<?php
include '../header.php';

// جلب جميع المستخدمين
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">إدارة المستخدمين</h1>
        <a href="add_user.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus ml-2"></i>إضافة مستخدم جديد
        </a>
    </div>

    <!-- جدول المستخدمين -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">المستخدم</th>
                        <th class="px-6 py-3">البريد الإلكتروني</th>
                        <th class="px-6 py-3">الدور</th>
                        <th class="px-6 py-3">الحالة</th>
                        <th class="px-6 py-3">تاريخ التسجيل</th>
                        <th class="px-6 py-3">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo $user['name']; ?></div>
                        </td>
                        <td class="px-6 py-4"><?php echo $user['email']; ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $roles = [
                                'admin' => 'مدير',
                                'manager' => 'مسؤول',
                                'employee' => 'موظف'
                            ];
                            echo $roles[$user['role']] ?? $user['role'];
                            ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($user['status'] == 'active'): ?>
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">نشط</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">غير نشط</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo $user['name']; ?>')" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($users)): ?>
        <div class="text-center py-8">
            <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">لا توجد مستخدمين</p>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteUser(id, name) {
    confirmDelete(`هل تريد حذف المستخدم "${name}"؟`).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_user.php?id=${id}`;
        }
    });
}
</script>

<?php include '../footer.php'; ?>