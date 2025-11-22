<?php
include '../header.php';

// البحث والتصفية
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$sql = "SELECT * FROM invoices WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (customer_name LIKE ? OR customer_phone LIKE ? OR id = ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $search;
}

if (!empty($date_from)) {
    $sql .= " AND DATE(created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND DATE(created_at) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$invoices = $stmt->fetchAll();
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">سجل الفواتير</h1>

    <!-- شريط البحث والتصفية -->
    <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">بحث</label>
                <input type="text" name="search" value="<?php echo $search; ?>" 
                       placeholder="ابحث بالاسم، الهاتف أو رقم الفاتورة..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">
                    <i class="fas fa-search ml-2"></i>بحث
                </button>
            </div>
        </form>
    </div>

    <!-- جدول الفواتير -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">رقم الفاتورة</th>
                        <th class="px-6 py-3">العميل</th>
                        <th class="px-6 py-3">المبلغ الإجمالي</th>
                        <th class="px-6 py-3">الربح</th>
                        <th class="px-6 py-3">طريقة الدفع</th>
                        <th class="px-6 py-3">التاريخ</th>
                        <th class="px-6 py-3">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">#<?php echo $invoice['id']; ?></td>
                        <td class="px-6 py-4">
                            <div><?php echo $invoice['customer_name']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $invoice['customer_phone']; ?></div>
                        </td>
                        <td class="px-6 py-4"><?php echo number_format($invoice['total_amount'], 2); ?> ر.س</td>
                        <td class="px-6 py-4"><?php echo number_format($invoice['profit'], 2); ?> ر.س</td>
                        <td class="px-6 py-4">
                            <?php
                            $payment_methods = [
                                'cash' => 'نقدي',
                                'transfer' => 'تحويل',
                                'card' => 'بطاقة',
                                'later' => 'آجل'
                            ];
                            echo $payment_methods[$invoice['payment_method']] ?? $invoice['payment_method'];
                            ?>
                        </td>
                        <td class="px-6 py-4"><?php echo date('Y-m-d H:i', strtotime($invoice['created_at'])); ?></td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="invoice_view.php?id=<?php echo $invoice['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="invoice_print.php?id=<?php echo $invoice['id']; ?>" 
                                   class="text-green-600 hover:text-green-900" title="طباعة">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($invoices)): ?>
        <div class="text-center py-8">
            <i class="fas fa-receipt text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">لا توجد فواتير</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>