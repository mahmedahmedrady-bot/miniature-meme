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

<div class="container mx-auto max-w-4xl">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <!-- رأس الفاتورة -->
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">فاتورة مبيعات</h1>
                <p class="text-gray-600">رقم الفاتورة: #<?php echo $invoice['id']; ?></p>
            </div>
            <div class="text-left">
                <p class="text-gray-600">التاريخ: <?php echo date('Y-m-d', strtotime($invoice['created_at'])); ?></p>
                <p class="text-gray-600">الوقت: <?php echo date('H:i', strtotime($invoice['created_at'])); ?></p>
            </div>
        </div>

        <!-- معلومات العميل -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-bold text-gray-800 mb-2">معلومات العميل</h3>
                <p class="text-gray-600">الاسم: <?php echo $invoice['customer_name']; ?></p>
                <?php if (!empty($invoice['customer_phone'])): ?>
                <p class="text-gray-600">الهاتف: <?php echo $invoice['customer_phone']; ?></p>
                <?php endif; ?>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-bold text-gray-800 mb-2">معلومات الدفع</h3>
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
            <h3 class="font-bold text-gray-800 mb-4">تفاصيل الفاتورة</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">المنتج</th>
                            <th class="px-4 py-3">السعر</th>
                            <th class="px-4 py-3">الكمية</th>
                            <th class="px-4 py-3">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr class="border-b">
                            <td class="px-4 py-3">
                                <div class="font-medium"><?php echo $item['product_name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $item['product_code']; ?></div>
                            </td>
                            <td class="px-4 py-3"><?php echo number_format($item['price'], 2); ?> ر.س</td>
                            <td class="px-4 py-3"><?php echo $item['quantity']; ?></td>
                            <td class="px-4 py-3 font-medium">
                                <?php echo number_format($item['price'] * $item['quantity'], 2); ?> ر.س
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- الملخص -->
        <div class="border-t pt-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">الإجمالي:</span>
                <span class="font-bold text-lg"><?php echo number_format($invoice['total_amount'], 2); ?> ر.س</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">الربح:</span>
                <span class="font-bold text-green-600"><?php echo number_format($invoice['profit'], 2); ?> ر.س</span>
            </div>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="flex justify-end space-x-3 space-x-reverse mt-8 pt-6 border-t">
            <a href="invoices_list.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">
                رجوع
            </a>
            <a href="invoice_print.php?id=<?php echo $invoice_id; ?>" target="_blank" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                <i class="fas fa-print ml-2"></i>طباعة
            </a>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>