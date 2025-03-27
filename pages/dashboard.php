<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
redirectIfNotLoggedIn();
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Trang chủ</h2>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Nhân viên</div>
            <div class="card-body">
                <?php
                $stmt = $conn->query("SELECT COUNT(*) FROM nhanvien");
                $count = $stmt->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $count; ?></h5>
                <p class="card-text">Tổng số nhân viên</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Phòng ban</div>
            <div class="card-body">
                <?php
                $stmt = $conn->query("SELECT COUNT(*) FROM phongban");
                $count = $stmt->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo $count; ?></h5>
                <p class="card-text">Tổng số phòng ban</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Lương trung bình</div>
            <div class="card-body">
                <?php
                $stmt = $conn->query("SELECT AVG(luong) FROM nhanvien");
                $avg = $stmt->fetchColumn();
                ?>
                <h5 class="card-title"><?php echo number_format($avg, 0); ?></h5>
                <p class="card-text">Lương trung bình</p>
            </div>
        </div>
    </div>
</div>

<?php if (isAdmin()): ?>
<div class="alert alert-info mt-4">
    Bạn đang đăng nhập với quyền quản trị viên (Admin).
</div>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>