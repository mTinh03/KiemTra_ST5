// Hiệu ứng loading
document.addEventListener('DOMContentLoaded', function() {
    // Hiệu ứng tooltip
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Hiệu ứng khi click button
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.add('active');
            setTimeout(() => {
                this.classList.remove('active');
            }, 150);
        });
    });
    
    // Toast thông báo
    const toastElList = [].slice.call(document.querySelectorAll('.toast'))
    const toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 })
    });
    toastList.forEach(toast => toast.show());
});

// Xác nhận trước khi xóa
function confirmDelete() {
    return confirm('Bạn có chắc chắn muốn xóa nhân viên này?');
}