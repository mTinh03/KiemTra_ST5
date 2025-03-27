<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$ma_nv = $_GET['id'] ?? '';
if (empty($ma_nv)) {
    header("Location: employees.php");
    exit();
}

$departments = $conn->query("SELECT * FROM phongban")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM nhanvien WHERE ma_nv = ?");
$stmt->execute([$ma_nv]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    header("Location: employees.php");
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_nv = trim($_POST['ten_nv']);
    $phai = $_POST['phai'];
    $noi_sinh = trim($_POST['noi_sinh']);
    $ma_phong = $_POST['ma_phong'];
    $luong = trim($_POST['luong']);
    
    // Validate
    if (empty($ten_nv)) $errors[] = "Tên nhân viên không được để trống";
    if (empty($phai)) $errors[] = "Giới tính không được để trống";
    if (empty($ma_phong)) $errors[] = "Phòng ban không được để trống";
    if (!is_numeric($luong)) $errors[] = "Lương phải là số";
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                UPDATE nhanvien 
                SET ten_nv = ?, phai = ?, noi_sinh = ?, ma_phong = ?, luong = ?
                WHERE ma_nv = ?
            ");
            $stmt->execute([$ten_nv, $phai, $noi_sinh, $ma_phong, $luong, $ma_nv]);
            
            $_SESSION['success'] = "Cập nhật nhân viên thành công";
            header("Location: employees.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Lỗi khi cập nhật nhân viên: " . $e->getMessage();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">SỬA THÔNG TIN NHÂN VIÊN</h2>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST">
    <div class="row mb-3">
        <label for="ma_nv" class="col-sm-2 col-form-label">Mã nhân viên</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="ma_nv" value="<?php echo $employee['ma_nv']; ?>" readonly>
        </div>
    </div>
    <div class="row mb-3">
        <label for="ten_nv" class="col-sm-2 col-form-label">Tên nhân viên</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="ten_nv" name="ten_nv" 
                   value="<?php echo htmlspecialchars($employee['ten_nv']); ?>" required>
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Giới tính</label>
        <div class="col-sm-10">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="phai" id="phai_nam" 
                       value="NAM" <?php echo $employee['phai'] === 'NAM' ? 'checked' : ''; ?> required>
                <label class="form-check-label" for="phai_nam">
                    <img src="../assets/images/man.jpg" width="30" alt="Nam"> Nam
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="phai" id="phai_nu" 
                       value="NU" <?php echo $employee['phai'] === 'NU' ? 'checked' : ''; ?>>
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
                   value="<?php echo htmlspecialchars($employee['noi_sinh']); ?>">
        </div>
    </div>
    <div class="row mb-3">
        <label for="ma_phong" class="col-sm-2 col-form-label">Phòng ban</label>
        <div class="col-sm-10">
            <select class="form-select" id="ma_phong" name="ma_phong" required>
                <option value="">Chọn phòng ban</option>
                <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept['ma_phong']; ?>" 
                    <?php echo $dept['ma_phong'] === $employee['ma_phong'] ? 'selected' : ''; ?>>
                    <?php echo $dept['ten_phong']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <label for="luong" class="col-sm-2 col-form-label">Lương</label>
        <div class="col-sm-10">
            <input type="number" class="form-control" id="luong" name="luong" 
                   value="<?php echo $employee['luong']; ?>" required>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-10 offset-sm-2">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="employees.php" class="btn btn-secondary">Hủy bỏ</a>
        </div>
    </div>
</form>
<?php include '../includes/footer.php'; ?>