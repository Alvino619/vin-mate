<?php
include 'config/database.php';
include 'includes/header.php';

// Initialize variables with default values
$total_items = 0;
$available_items = 0;
$unavailable_items = 0;
$total_value = 0;

try {
    // Menghitung total barang
    $sql_total = "SELECT COUNT(*) as total FROM tb_inventory";
    $result_total = $conn->query($sql_total);
    if ($result_total) {
        $row_total = $result_total->fetch_assoc();
        $total_items = $row_total['total'] ?? 0;
    }

    // Menghitung barang yang tersedia
    $sql_available = "SELECT COUNT(*) as available FROM tb_inventory WHERE status_barang = 1";
    $result_available = $conn->query($sql_available);
    if ($result_available) {
        $row_available = $result_available->fetch_assoc();
        $available_items = $row_available['available'] ?? 0;
    }

    // Menghitung barang yang tidak tersedia
    $sql_unavailable = "SELECT COUNT(*) as unavailable FROM tb_inventory WHERE status_barang = 0";
    $result_unavailable = $conn->query($sql_unavailable);
    if ($result_unavailable) {
        $row_unavailable = $result_unavailable->fetch_assoc();
        $unavailable_items = $row_unavailable['unavailable'] ?? 0;
    }

    // Menghitung total nilai inventory
    $sql_value = "SELECT SUM(jumlah_barang * harga_beli) as total_value FROM tb_inventory";
    $result_value = $conn->query($sql_value);
    if ($result_value) {
        $row_value = $result_value->fetch_assoc();
        $total_value = $row_value['total_value'] ?? 0;
    }
} catch (Exception $e) {
    // Handle errors gracefully
    error_log("Database error: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2 me-2"></i> Dashboard</h1>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Barang</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($total_items) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Barang Tersedia</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($available_items) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card danger h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Barang Habis</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($unavailable_items) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card info h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Nilai Inventory</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($total_value, 0, ',', '.') ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Items -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Barang dengan Stok Menipis (< 10)
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $sql = "SELECT * FROM tb_inventory WHERE jumlah_barang < 10 ORDER BY jumlah_barang ASC";
                        $result = $conn->query($sql);
                        
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $status = $row['status_barang'] ? 
                                    '<span class="badge bg-success">Available</span>' : 
                                    '<span class="badge bg-danger">Not Available</span>';
                                echo '<tr>
                                    <td>'.htmlspecialchars($row['kode_barang']).'</td>
                                    <td>'.htmlspecialchars($row['nama_barang']).'</td>
                                    <td class="'.($row['jumlah_barang'] < 5 ? 'text-danger fw-bold' : '').'">'.htmlspecialchars($row['jumlah_barang']).'</td>
                                    <td>'.htmlspecialchars($row['satuan_barang']).'</td>
                                    <td>'.$status.'</td>
                                    <td>
                                        <a href="restock_item.php?id='.$row['id_barang'].'" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i>Tambah Stok
                                        </a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data barang dengan stok menipis</td></tr>';
                        }
                    } catch (Exception $e) {
                        echo '<tr><td colspan="6" class="text-center text-danger py-4">Error: '.htmlspecialchars($e->getMessage()).'</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>