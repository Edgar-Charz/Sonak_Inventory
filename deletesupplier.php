<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (isset($_GET['id'])) {
    $supplier_id = intval($_GET['id']);

    // Deleting supplier
    $delete_supplier_query = $conn->prepare("DELETE FROM suppliers WHERE supplierId = ?");
    $delete_supplier_query->bind_param("i", $supplier_id);

    if ($delete_supplier_query->execute()) {
        header("Location: supplierlist.php?status=success");
        exit;
    } else {
        header("Location: supplierlist.php?status=error");
        exit;
    }
} else {
    header("Location: supplierlist.php?status=error");
}
exit;
