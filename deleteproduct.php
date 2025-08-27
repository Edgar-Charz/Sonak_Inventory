<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Deleting product
    $delete_product_query = $conn->prepare("DELETE FROM products WHERE productId = ?");
    $delete_product_query->bind_param("i", $product_id);

    if ($delete_product_query->execute()) {
        header("Location: productlist.php?status=success");
        exit;
    } else {
        header("Location: productlist.php?status=error");
        exit;
    }
} else {
    header("Location: productlist.php?status=error");
}
exit;
