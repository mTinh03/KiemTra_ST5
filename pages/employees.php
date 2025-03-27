<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
redirectIfNotLoggedIn();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$offset = ($page - 1) * $perPage;

// Đếm tổng số nhân viên
$total = $conn->query("SELECT COUNT(*) FROM nhanvien")->fetchColumn();
$totalPages = ceil($total / $perPage);

// Lấy danh sách nhân viên
$stmt = $conn->prepare("
    SELECT n.*, p.ten_phong 
    FROM nhanvien n 
    LEFT JOIN phongban p ON n.ma_phong = p.ma_phong 
    ORDER BY n.ma_nv 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h2 class="mb-4">DANH SÁCH NHÂN VIÊN</h2>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Mã NV</th>
                <th>Tên NV</th>
                <th>Giới tính</th>
                <th>Nơi sinh</th>
                <th>Phòng</th>
                <th>Lương</th>
                <?php if (isAdmin()): ?>
                <th>Thao tác</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?= htmlspecialchars($emp['ma_nv']) ?></td>
                <td><?= htmlspecialchars($emp['ten_nv']) ?></td>
                <td>
                    <img src="../assets/images/<?= $emp['phai'] === 'NU' ? 'woman.jpg' : 'man.jpg' ?>" 
                         width="30" alt="<?= $emp['phai'] ?>">
                </td>
                <td><?= htmlspecialchars($emp['noi_sinh']) ?></td>
                <td><?= htmlspecialchars($emp['ten_phong']) ?></td>
                <td><?= number_format($emp['luong']) ?></td>
                <?php if (isAdmin()): ?>
                <td>
                    <a href="edit_employee.php?id=<?= $emp['ma_nv'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                    <a href="delete_employee.php?id=<?= $emp['ma_nv'] ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Xóa nhân viên này?')">Xóa</a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Phân trang -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $page - 1 ?>">Trước</a>
        </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $page + 1 ?>">Sau</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>

<?php include '../includes/footer.php'; ?>