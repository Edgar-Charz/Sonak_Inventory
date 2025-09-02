<?php
if (isset($_POST['updateCategory'])) {
    $id = $_POST['categoryId'];
    $name = $_POST['categoryName'];
    $type = $_POST['categoryType'];

    // DB connection here
    include 'includes/db_connection.php';

    $sql = "UPDATE categories SET categoryName=?, categoryType=? WHERE categoryId=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $type, $id);

    if ($stmt->execute()) {
        header("Location: categorylist.php?updated=1");
    } else {
        header("Location: categorylist.php?error=1");
    }
}
