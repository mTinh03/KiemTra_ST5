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

<form method="POST">
    <div class="row mb-3">
        <label for="ma_nv" class="col-sm-2 col-form-label">Mã nhân viên</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="ma_nv" name="ma_nv" 
                   value="<?php echo htmlspecialchars($ma_nv); ?>" readonly>
        </div>
    </div>
    
    <div class="row mb-3">
        <label for="ten_nv" class="col-sm-2 col-form-label">Tên nhân viên</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="ten_nv" name="ten_nv" 
                   value="<?php echo htmlspecialchars($_POST['ten_nv'] ?? ''); ?>" required>
        </div>
    </div>
    
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Giới tính</label>
        <div class="col-sm-10">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="phai" id="phai_nam" value="NAM"
                       <?php echo ($_POST['phai'] ?? '') === 'NAM' ? 'checked' : ''; ?> required>
                <label class="form-check-label" for="phai_nam">
                    <img src="../assets/images/man.jpg" width="30" alt="Nam"> Nam
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="phai" id="phai_nu" value="NU"
                       <?php echo ($_POST['phai'] ?? '') === 'NU' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="phai_nu">
                    <img src="../assets/images/woman.jpg" width="30" alt="Nữ"> Nữ
                </label>
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <label for="noi_sinh" class="col-sm-2 col-form-label">Nơi sinh</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="noi_sinh" name="noi_sinh" 
                   value="<?php echo htmlspecialchars($_POST['noi_sinh'] ?? ''); ?>">
        </div>
    </div>
    
    <div class="row mb-3">
        <label for="ma_phong" class="col-sm-2 col-form-label">Phòng ban</label>
        <div class="col-sm-10">
            <select class="form-select" id="ma_phong" name="ma_phong" required>
                <option value="">Chọn phòng ban</option>
                <?php foreach ($departments as $dept): ?>
                <option value="<?php echo htmlspecialchars($dept['ma_phong']); ?>"
                    <?php echo ($_POST['ma_phong'] ?? '') === $dept['ma_phong'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($dept['ten_phong']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="row mb-3">
        <label for="luong" class="col-sm-2 col-form-label">Lương</label>
        <div class="col-sm-10">
            <input type="number" class="form-control" id="luong" name="luong" 
                   value="<?php echo htmlspecialchars($_POST['luong'] ?? ''); ?>" required min="0">
        </div>
    </div>
    
    <div class="row">
        <div class="col-sm-10 offset-sm-2">
            <button type="submit" class="btn btn-primary">Thêm nhân viên</button>
            <a href="employees.php" class="btn btn-secondary">Hủy bỏ</a>
        </div>
    </div>
</form>

<?php include '../includes/footer.php'; ?>