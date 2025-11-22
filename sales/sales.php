<?php
include '../header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // معالجة إنشاء الفاتورة
    $customer_name = cleanInput($_POST['customer_name']);
    $customer_phone = cleanInput($_POST['customer_phone']);
    $payment_method = cleanInput($_POST['payment_method']);
    $products = $_POST['products'];
    
    try {
        $pdo->beginTransaction();
        
        // حساب الإجمالي والربح
        $total_amount = 0;
        $total_profit = 0;
        
        foreach ($products as $product) {
            $product_id = $product['id'];
            $quantity = $product['quantity'];
            $price = $product['price'];
            
            // جلب سعر التكلفة
            $stmt = $pdo->prepare("SELECT cost_price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product_data = $stmt->fetch();
            
            $cost = $product_data['cost_price'];
            $total_amount += $price * $quantity;
            $total_profit += ($price - $cost) * $quantity;
        }
        
        // إدخال الفاتورة
        $stmt = $pdo->prepare("INSERT INTO invoices (customer_name, customer_phone, total_amount, profit, payment_method) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$customer_name, $customer_phone, $total_amount, $total_profit, $payment_method]);
        $invoice_id = $pdo->lastInsertId();
        
        // إدخال منتجات الفاتورة وتحديث المخزون
        foreach ($products as $product) {
            $product_id = $product['id'];
            $quantity = $product['quantity'];
            $price = $product['price'];
            
            $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$invoice_id, $product_id, $quantity, $price]);
            
            // تحديث المخزون
            $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $product_id]);
        }
        
        $pdo->commit();
        $success = "تم إنشاء الفاتورة بنجاح! رقم الفاتورة: #" . $invoice_id;
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "حدث خطأ في إنشاء الفاتورة: " . $e->getMessage();
    }
}

// جلب المنتجات
$products = $pdo->query("SELECT * FROM products WHERE quantity > 0 ORDER BY name")->fetchAll();
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">إنشاء فاتورة جديدة</h1>

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

    <form method="POST" action="" id="invoiceForm">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- بيانات العميل -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">بيانات العميل</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسم العميل</label>
                        <input type="text" name="customer_name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                        <input type="text" name="customer_phone" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع</label>
                        <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">نقدي</option>
                            <option value="transfer">تحويل بنكي</option>
                            <option value="card">بطاقة ائتمان</option>
                            <option value="later">آجل</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- إضافة المنتجات -->
            <div class="bg-white rounded-lg shadow-lg p-6 lg:col-span-2">
                <h2 class="text-lg font-bold text-gray-800 mb-4">إضافة المنتجات</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">اختر المنتج</label>
                    <select id="productSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">اختر منتج</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" 
                                    data-name="<?php echo $product['name']; ?>"
                                    data-price="<?php echo $product['selling_price']; ?>"
                                    data-stock="<?php echo $product['quantity']; ?>">
                                <?php echo $product['name']; ?> - <?php echo number_format($product['selling_price'], 2); ?> ر.س (المخزون: <?php echo $product['quantity']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" onclick="addProduct()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md mb-4">
                    <i class="fas fa-plus ml-2"></i>إضافة المنتج
                </button>

                <!-- قائمة المنتجات المضافة -->
                <div id="productsList" class="space-y-3">
                    <!-- سيتم إضافة المنتجات هنا ديناميكياً -->
                </div>

                <!-- الملخص -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium">الإجمالي:</span>
                        <span id="totalAmount" class="font-bold text-lg">0.00 ر.س</span>
                    </div>
                </div>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md w-full mt-4">
                    <i class="fas fa-receipt ml-2"></i>إنشاء الفاتورة
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let products = [];

function addProduct() {
    const select = document.getElementById('productSelect');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!selectedOption.value) return;
    
    const productId = selectedOption.value;
    const productName = selectedOption.getAttribute('data-name');
    const productPrice = parseFloat(selectedOption.getAttribute('data-price'));
    const productStock = parseInt(selectedOption.getAttribute('data-stock'));
    
    // التحقق من عدم إضافة المنتج مسبقاً
    if (products.find(p => p.id == productId)) {
        alert('هذا المنتج مضاف مسبقاً');
        return;
    }
    
    products.push({
        id: productId,
        name: productName,
        price: productPrice,
        stock: productStock,
        quantity: 1
    });
    
    updateProductsList();
    updateTotal();
}

function updateProductsList() {
    const container = document.getElementById('productsList');
    container.innerHTML = '';
    
    products.forEach((product, index) => {
        const productHTML = `
            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                <div class="flex-1">
                    <div class="font-medium">${product.name}</div>
                    <div class="text-sm text-gray-500">${product.price} ر.س للوحدة</div>
                </div>
                <div class="flex items-center space-x-2 space-x-reverse">
                    <input type="number" min="1" max="${product.stock}" value="${product.quantity}" 
                           onchange="updateQuantity(${index}, this.value)" 
                           class="w-20 px-2 py-1 border border-gray-300 rounded text-center">
                    <span class="text-gray-600">× ${product.price} ر.س</span>
                    <span class="font-medium">= ${(product.quantity * product.price).toFixed(2)} ر.س</span>
                    <button type="button" onclick="removeProduct(${index})" class="text-red-600 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        container.innerHTML += productHTML;
    });
}

function updateQuantity(index, quantity) {
    quantity = parseInt(quantity);
    if (quantity < 1) quantity = 1;
    if (quantity > products[index].stock) quantity = products[index].stock;
    
    products[index].quantity = quantity;
    updateProductsList();
    updateTotal();
}

function removeProduct(index) {
    products.splice(index, 1);
    updateProductsList();
    updateTotal();
}

function updateTotal() {
    const total = products.reduce((sum, product) => sum + (product.price * product.quantity), 0);
    document.getElementById('totalAmount').textContent = total.toFixed(2) + ' ر.س';
}

// إعداد نموذج الإرسال
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    if (products.length === 0) {
        e.preventDefault();
        alert('يرجى إضافة منتجات على الأقل');
        return;
    }
    
    // إضافة المنتجات إلى النموذج كحقول مخفية
    products.forEach((product, index) => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = `products[${index}][id]`;
        idInput.value = product.id;
        this.appendChild(idInput);
        
        const quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = `products[${index}][quantity]`;
        quantityInput.value = product.quantity;
        this.appendChild(quantityInput);
        
        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = `products[${index}][price]`;
        priceInput.value = product.price;
        this.appendChild(priceInput);
    });
});
</script>

<?php include '../footer.php'; ?>