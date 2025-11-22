<?php
include '../header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_backup'])) {
    $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = __DIR__ . '/' . $backup_file;
    
    try {
        // الحصول على جميع الجداول
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        $sql = "-- نسخة احتياطية لنظام إدارة المصنع\n";
        $sql .= "-- تم الإنشاء في: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            // هيكل الجدول
            $sql .= "--\n-- هيكل الجدول: $table\n--\n";
            $create_table = $pdo->query("SHOW CREATE TABLE $table")->fetch();
            $sql .= $create_table['Create Table'] . ";\n\n";
            
            // بيانات الجدول
            $sql .= "--\n-- بيانات الجدول: $table\n--\n";
            $rows = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES \n";
                
                $values = [];
                foreach ($rows as $row) {
                    $row_values = array_map(function($value) use ($pdo) {
                        if ($value === null) return 'NULL';
                        return $pdo->quote($value);
                    }, $row);
                    $values[] = "(" . implode(', ', $row_values) . ")";
                }
                
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        // حفظ النسخة الاحتياطية
        if (file_put_contents($backup_path, $sql)) {
            $success = "تم إنشاء النسخة الاحتياطية بنجاح: " . $backup_file;
        } else {
            $error = "فشل في إنشاء النسخة الاحتياطية";
        }
        
    } catch(PDOException $e) {
        $error = "حدث خطأ في إنشاء النسخة الاحتياطية: " . $e->getMessage();
    }
}

// جلب قائمة النسخ الاحتياطية
$backup_files = [];
if (is_dir(__DIR__)) {
    $files = scandir(__DIR__);
    foreach ($files as $file) {
        if (preg_match('/^backup_.*\.sql$/', $file)) {
            $file_path = __DIR__ . '/' . $file;
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'date' => date('Y-m-d H:i:s', filemtime($file_path))
            ];
        }
    }
    
    // ترتيب الملفات من الأحدث إلى الأقدم
    usort($backup_files, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">النسخ الاحتياطي</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- إنشاء نسخة احتياطية -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">إنشاء نسخة احتياطية</h2>
            
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

            <div class="bg-blue-50 p-4 rounded-lg mb-4">
                <p class="text-blue-700">
                    <i class="fas fa-info-circle ml-2"></i>
                    سيتم إنشاء نسخة احتياطية كاملة من قاعدة البيانات وتحميلها على الخادم.
                </p>
            </div>

            <form method="POST" action="">
                <button type="submit" name="create_backup" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md w-full">
                    <i class="fas fa-database ml-2"></i>إنشاء نسخة احتياطية الآن
                </button>
            </form>
        </div>

        <!-- قائمة النسخ الاحتياطية -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">النسخ الاحتياطية السابقة</h2>
            
            <?php if (!empty($backup_files)): ?>
                <div class="space-y-3">
                    <?php foreach ($backup_files as $backup): ?>
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900"><?php echo $backup['name']; ?></div>
                            <div class="text-sm text-gray-500">
                                <?php echo $backup['date']; ?> - 
                                <?php echo round($backup['size'] / 1024, 2); ?> KB
                            </div>
                        </div>
                        <div class="flex space-x-2 space-x-reverse">
                            <a href="<?php echo $backup['name']; ?>" download 
                               class="text-green-600 hover:text-green-900">
                                <i class="fas fa-download"></i>
                            </a>
                            <button onclick="deleteBackup('<?php echo $backup['name']; ?>')" 
                                    class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-database text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">لا توجد نسخ احتياطية</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteBackup(filename) {
    confirmDelete(`هل تريد حذف النسخة الاحتياطية "${filename}"؟`).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_backup.php?file=${filename}`;
        }
    });
}
</script>

<?php include '../footer.php'; ?>