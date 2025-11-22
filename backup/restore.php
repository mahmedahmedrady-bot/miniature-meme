<?php
include '../header.php';

// هذا الملف سيكون مسؤولاً عن استرجاع النسخ الاحتياطية
// سيتم تنفيذ المنطق الفعلي للاسترجاع هنا

?>

<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
        <i class="fas fa-database text-6xl text-blue-500 mb-6"></i>
        <h1 class="text-2xl font-bold text-gray-800 mb-4">استرجاع نسخة احتياطية</h1>
        <p class="text-gray-600 mb-6">سيتم تطوير وظيفة الاسترجاع في المرحلة القادمة</p>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-yellow-800">
                <i class="fas fa-exclamation-triangle ml-2"></i>
                هذه الوظيفة قيد التطوير وسيتم إضافتها قريباً
            </p>
        </div>
        
        <a href="backup.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
            <i class="fas fa-arrow-right ml-2"></i>العودة إلى النسخ الاحتياطي
        </a>
    </div>
</div>

<?php include '../footer.php'; ?>