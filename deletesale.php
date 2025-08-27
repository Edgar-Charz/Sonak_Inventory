<?php
include("includes/db_connection.php");
include("includes/session.php");

if (isset($_GET["id"])) {    
    $order_uid = $_GET["id"];

    // Deleting order
    $delete_order_query = $conn->prepare("DELETE FROM orders WHERE orderUId = ?");
    $delete_order_query->bind_param("s", $order_uid);

    if ($delete_order_query->execute()) { 
    header("Location: saleslist.php?status=success");
    exit;
} else {
    header("Location: saleslist.php?status=error");
    exit;
}
} else {
    header("Location: saleslist.php?status=error");
}
exit;
?>