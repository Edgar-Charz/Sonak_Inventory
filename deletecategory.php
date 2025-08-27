<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (isset($_GET['categoryId'])) {
    $category_id = $_GET['categoryId'];

    // Prepare a delete statement
    $delete_category_query = $conn->prepare("DELETE FROM categories WHERE categoryId = ?");
    $delete_category_query->bind_param("s", $category_id);

    if ($delete_category_query->execute()) {

        // Successfully deleted, then  Redirect to category list with success flag
        header("Location: categorylist.php?deleted=1");
        exit();
    } else {

        // Failed to delete, then Redirect with error flag
        header("Location: categorylist.php?error=1");
        exit();
    }
} else if (isset($_GET['unitId'])) {
    $category_id = $_GET['unitId'];

    // Prepare a delete statement
    $delete_category_query = $conn->prepare("DELETE FROM units WHERE unitId = ?");
    $delete_category_query->bind_param("s", $category_id);

    if ($delete_category_query->execute()) {

        // Successfully deleted
        header("Location: categorylist.php?deleted=1");
        exit();
    } else {

        // Failed to delete
        header("Location: categorylist.php?error=1");
        exit();
    }
}
