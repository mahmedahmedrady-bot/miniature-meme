<?php
include '../header.php';

$invoice_id = $_GET['id'] ?? 0;

if (!$invoice_id) {
    redirect('invoices_list.php');
}

// جلب بيانات الفاتورة
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    redirect('invoices_list.php');
}

// جلب عناصر الفاتورة
$stmt = $pdo->prepare("SELECT ii.*, p.name as product_name, p.code as product_code 
                       FROM invoice_items ii 
                       JOIN products p ON ii.product_id = p.id 
                       WHERE ii.invoice_id = ?");
$stmt->execute([$invoice_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة فاتورة #<?php echo $invoice_id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
            .print-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-white">
    <div class="container mx-auto max-w-4xl p-8">
        <!-- رأس الفاتورة -->
        <div class="flex justify-between items-start mb-8 border-b-2 border-gray-300 pb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">فاتورة مبيعات</h1>
                <p class="text-gray-600 text-lg">رقم الفاتورة: #<?php echo $invoice['id']; ?></p>
            </div>
            <div class="text-left">
                <p class="text-gray-600">التاريخ: <?php echo date('Y-m-d', strtotime($invoice['created_at'])); ?></p>
                <p class="text-gray-600">الوقت: <?php echo date('H:i', strtotime($invoice['created_at'])); ?></p>
            </div>
        </div>

        <!-- معلومات العميل -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-100 p-4 rounded-lg">
                <h3 class="font-bold text-gray-800 mb-2 text-lg">معلومات العميل</h3>
                <p class="text-gray-600">الاسم: <?php echo $invoice['customer_name']; ?></p>
                <?php if (!empty($invoice['customer_phone'])): ?>
                <p class="text-gray-600">الهاتف: <?php echo $invoice['customer_phone']; ?></p>
                <?php endif; ?>
            </div>
            <div class="bg-gray-100 p-4 rounded-lg">
                <h3 class="font-bold text-gray-800 mb-2 text-lg">معلومات الدفع</h3>
                <p class="text-gray-600">
                    طريقة الدفع: 
                    <?php
                    $payment_methods = [
                        'cash' => 'نقدي',
                        'transfer' => 'تحويل بنكي',
                        'card' => 'بطاقة ائتمان',
                        'later' => 'آجل'
                    ];
                    echo $payment_methods[$invoice['payment_method']] ?? $invoice['payment_method'];
                    ?>
                </p>
            </div>
        </div>

        <!-- تفاصيل الفاتورة -->
        <div class="mb-8">
            <h3 class="font-bold text-gray-800 mb-4 text-lg">تفاصيل الفاتورة</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 border-collapse border border-gray-300">
                    <thead class="text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th class="border border-gray-300 px-4 py-3">المنتج</th>
                            <th class="border border-gray-300 px-4 py-3">السعر</th>
                            <th class="border border-gray-300 px-4 py-3">الكمية</th>
                            <th class="border border-gray-300 px-4 py-3">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr class="border-b">
                            <td class="border border-gray-300 px-4 py-3">
                                <div class="font-medium"><?php echo $item['product_name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $item['product_code']; ?></div>
                            </td>
                            <td class="border border-gray-300 px-4 py-3"><?php echo number_format($item['price'], 2); ?> ر.س</td>
                            <td class="border border-gray-300 px-4 py-3"><?php echo $item['quantity']; ?></td>
                            <td class="border border-gray-300 px-4 py-3 font-medium">
                                <?php echo number_format($item['price'] * $item['quantity'], 2); ?> ر.س
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- الملخص -->
        <div class="border-t-2 border-gray-300 pt-4">
            <div class="flex justify-between items-center mb-2 text-lg">
                <span class="text-gray-600 font-bold">الإجمالي:</span>
                <span class="font-bold text-xl"><?php echo number_format($invoice['total_amount'], 2); ?> ر.س</span>
            </div>
        </div>

        <!-- التوقيع -->
        <div class="mt-12 pt-8 border-t-2 border-gray-300">
            <div class="flex justify-between">
                <div class="text-center">
                    <p class="mb-12 border-b border-gray-400 pb-1">توقيع العميل</p>
                    <p>الاسم: ________________</p>
                </div>
                <div class="text-center">
                    <p class="mb-12 border-b border-gray-400 pb-1">توقيع المسؤول</p>
                    <p>الاسم: ________________</p>
                </div>
            </div>
        </div>

        <!-- رسالة شكر -->
        <div class="text-center mt-8 pt-4 border-t border-gray-300">
            <p class="text-gray-600">شكراً لتعاملكم معنا</p>
            <p class="text-gray-500 text-sm">للاستفسار: 0512345678</p>
        </div>
    </div>

    <!-- أزرار التحكم في الطباعة -->
    <div class="no-print fixed bottom-4 right-4 space-x-2 space-x-reverse">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-lg">
            <i class="fas fa-print ml-2"></i>طباعة
        </button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg shadow-lg">
            <i class="fas fa-times ml-2"></i>إغلاق
        </button>
    </div>

    <script>
        // الطباعة تلقائياً عند فتح الصفحة
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>