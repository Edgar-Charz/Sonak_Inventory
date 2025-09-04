<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];
$targetDir = "assets/img/profiles/";
$targetPath = $targetDir . basename($_FILES["profile_photo"]["name"]);
$imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

// Validate image upload
if (isset($_POST["upload_photo"])) {

    // Check if file was selected
    if (!isset($_FILES["profile_photo"]) || $_FILES["profile_photo"]["error"] == UPLOAD_ERR_NO_FILE) {
        // No file uploaded
        header("Location: profile.php?status=nofile");
        exit();
    }

    // Validate image
    $check = getimagesize($_FILES["profile_photo"]["tmp_name"]);
    if ($check === false) {
        header("Location: profile.php?status=invalid");
        exit();
    }

    // Get file extension
    $imageFileType = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));

    // Optional: Rename file to avoid collisions
    $newFileName = "user_" . $userId . "_" . time() . "." . $imageFileType;
    $newFilePath = $targetDir . $newFileName;

    // Move uploaded file
    if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $newFilePath)) {
        $stmt = $conn->prepare("UPDATE users SET userPhoto = ? WHERE userId = ?");
        $stmt->bind_param("si", $newFileName, $userId);

        if ($stmt->execute()) {
            $_SESSION['profilePicture'] = $newFileName;
            header("Location: profile.php?status=success");
            exit();
        } else {
            header("Location: profile.php?status=dberror");
            exit();
        }
    } else {
        header("Location: profile.php?status=uploadfail");
        exit();
    }
}
