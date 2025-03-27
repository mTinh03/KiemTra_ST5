<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Kiểm tra đăng nhập với cả mật khẩu mã hóa và plain text
    if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        
        // Chuyển hướng dựa trên role
        if ($user['role'] === 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: employees.php");
        }
        exit();
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
    }
}
?>

<?php include '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">ĐĂNG NHẬP HỆ THỐNG</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i> Tên đăng nhập
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required
                               placeholder="Nhập tên đăng nhập">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Mật khẩu
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required
                               placeholder="Nhập mật khẩu">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-muted text-center">
                <small>Hệ thống Quản lý Nhân sự</small>
            </div>
        </div>
    </div>
</div>

<!-- Thêm Font Awesome cho icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    .card {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .card-header {
        padding: 1rem;
    }
</style>
<?php include '../includes/footer.php'; ?>