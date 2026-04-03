document.addEventListener('DOMContentLoaded', () => {

    const filterBtn = document.getElementById('filterBtn');
    const searchInput = document.getElementById('searchInput');


    filterBtn.addEventListener('click', () => {
        filterBtn.classList.toggle('active');
        
        if (filterBtn.classList.contains('active')) {
            filterBtn.style.backgroundColor = '#00B4D8';
            filterBtn.style.color = 'white';
            showToast('تم فتح الفلاتر');
        } else {
            filterBtn.style.backgroundColor = 'white';
            filterBtn.style.color = '#333';
            showToast('تم إغلاق الفلاتر');
        }
    });

   
    searchInput.addEventListener('input', (e) => {
        const value = e.target.value.trim();
        if (value.length > 0) {
            console.log('البحث عن:', value);
          
        }
    });

    
    function showToast(message) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            z-index: 10000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = '0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }

});

document.addEventListener("DOMContentLoaded", () => {

    // حذف النشاط
    document.querySelectorAll('.delete-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            if (confirm('هل أنت متأكد من حذف هذا النشاط؟')) {
                this.closest('tr').remove();
            }
        });
    });

    // تعديل النشاط
    document.querySelectorAll('.edit-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            alert('سيتم فتح نموذج تعديل النشاط');
        });
    });
});