<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

function generateEmployeeCode($conn) {
    $stmt = $conn->query("SELECT ma_nv FROM nhanvien ORDER BY ma_nv DESC LIMIT 1");
    $lastCode = $stmt->fetchColumn();
    
    if (!$lastCode) {
        return 'A01';
    }
    
    $letter = substr($lastCode, 0, 1);
    $number = (int) substr($lastCode, 1);
    
    if ($number >= 99) {
        $letter = chr(ord($letter) + 1);
        $number = 1;
    } else {
        $number++;
    }
    
    return $letter . str_pad($number, 2, '0', STR_PAD_LEFT);
}

$departments = $conn->query("SELECT * FROM phongban")->fetchAll(PDO::FETCH_ASSOC);
$ma_nv = generateEmployeeCode($conn);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sử dụng toán tử null coalescing (??) để tránh lỗi undefined array key
    $ma_nv = $_POST['ma_nv'] ?? '';
    $ten_nv = trim($_POST['ten_nv'] ?? '');
    $phai = $_POST['phai'] ?? '';
    $noi_sinh = trim($_POST['noi_sinh'] ?? '');
    $ma_phong = $_POST['ma_phong'] ?? '';
    $luong = $_POST['luong'] ?? 0;
    
    // Validate dữ liệu
    if (empty($ten_nv)) {
        $errors[] = "Tên nhân viên không được để trống";
    }
    
    if (empty($phai)) {
        $errors[] = "Vui lòng chọn giới tính";
    }
    
    if (empty($ma_phong)) {
        $errors[] = "Vui lòng chọn phòng ban";
    }
    
    if (!is_numeric($luong) || $luong <= 0) {
        $errors[] = "Lương phải là số dương";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO nhanvien (ma_nv, ten_nv, phai, noi_sinh, ma_phong, luong)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ma_nv, $ten_nv, $phai, $noi_sinh, $ma_phong, $luong]);
            
            $_SESSION['success'] = "Thêm nhân viên thành công";
            header("Location: employees.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Lỗi khi thêm nhân viên: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<h2 class="mb-4">THÊM NHÂN VIÊN MỚI</h2>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Thay thế form bằng phiên bản mới -->
<form method="POST" class="needs-validation" novalidate>
    <div class="card animated-card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-user-plus me-2"></i>Thông tin nhân viên</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="ma_nv" class="form-label">Mã nhân viên</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="ma_nv" name="ma_nv" 
                               value="<?php echo htmlspecialchars($ma_nv); ?>" readonly>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="ten_nv" class="form-label">Họ tên <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="ten_nv" name="ten_nv" 
                               value="<?php echo htmlspecialchars($_POST['ten_nv'] ?? ''); ?>" required>
                        <div class="invalid-feedback">Vui lòng nhập tên nhân viên</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Giới tính <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-inline flex-grow-1">
                            <input class="form-check-input" type="radio" name="phai" id="phai_nam" value="NAM"
                                   <?php echo ($_POST['phai'] ?? '') === 'NAM' ? 'checked' : ''; ?> required>
                            <label class="form-check-label d-flex align-items-center" for="phai_nam">
                                <img src="../assets/images/man.jpg" width="30" class="rounded-circle me-2" alt="Nam">
                                <span>Nam</span>
                            </label>
                        </div>
                        <div class="form-check form-check-inline flex-grow-1">
                            <input class="form-check-input" type="radio" name="phai" id="phai_nu" value="NU"
                                   <?php echo ($_POST['phai'] ?? '') === 'NU' ? 'checked' : ''; ?>>
                            <label class="form-check-label d-flex align-items-center" for="phai_nu">
                                <img src="../assets/images/woman.jpg" width="30" class="rounded-circle me-2" alt="Nữ">
                                <span>Nữ</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="ma_phong" class="form-label">Phòng ban <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-building"></i></span>
                        <select class="form-select" id="ma_phong" name="ma_phong" required>
                            <option value="">Chọn phòng ban...</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['ma_phong']); ?>"
                                <?php echo ($_POST['ma_phong'] ?? '') === $dept['ma_phong'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['ten_phong']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Vui lòng chọn phòng ban</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="noi_sinh" class="form-label">Nơi sinh</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-map-marker-alt"></i></span>
                        <input type="text" class="form-control" id="noi_sinh" name="noi_sinh" 
                               value="<?php echo htmlspecialchars($_POST['noi_sinh'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="luong" class="form-label">Lương <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white"><i class="fas fa-money-bill-wave"></i></span>
                        <input type="number" class="form-control" id="luong" name="luong" 
                               value="<?php echo htmlspecialchars($_POST['luong'] ?? ''); ?>" required min="0">
                        <span class="input-group-text">VND</span>
                        <div class="invalid-feedback">Vui lòng nhập lương hợp lệ</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between">
                <a href="employees.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Lưu thông tin
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Validation form
(function () {
    'use strict'
    
    const forms = document.querySelectorAll('.needs-validation')
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include '../includes/footer.php'; ?>