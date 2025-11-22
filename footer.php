        </main>
    </div>

    <!-- السكربتات -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // دوال JavaScript عامة
        function confirmDelete(message = 'هل أنت متأكد من الحذف؟') {
            return Swal.fire({
                title: 'تأكيد الحذف',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            });
        }

        function showAlert(title, message, type = 'success') {
            Swal.fire({
                title: title,
                text: message,
                icon: type,
                confirmButtonText: 'حسناً'
            });
        }

        // تحميل الصور مع معالجة الأخطاء
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.onerror = function() {
                    this.src = 'assets/images/placeholder.jpg';
                }
            });
        });
    </script>
</body>
</html>