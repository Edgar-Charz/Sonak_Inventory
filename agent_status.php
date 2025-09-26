<?php
include 'includes/db_connection.php';
include 'includes/session.php';

// Time zone setting
$time = new DateTime("now", new DateTimeZone("Africa/Dar_es_Salaam"));
$current_time = $time->format("Y-m-d H:i:s");

// Check if agent ID is provided
if (isset($_GET['id'])) {
    $agent_id = intval($_GET['id']);
    $current_time = date('Y-m-d H:i:s');

    // Check current status
    $status_stmt = $conn->prepare("SELECT agentStatus FROM agents WHERE agentId = ?");
    $status_stmt->bind_param("i", $agent_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result->num_rows === 1) {
        $agent = $status_result->fetch_assoc();
        $current_status = $agent['agentStatus'];

        //  Perform action based on status
        if ($current_status == 1) {
            // Agent is active → deactivate
            $update_stmt = $conn->prepare("UPDATE agents SET agentStatus = 0, updated_at = ? WHERE agentId = ?");
            $update_stmt->bind_param("si", $current_time, $agent_id);
            $update_stmt->execute();

            header("Location: agentlist.php?status=deactivated");
            exit;
        } else {
            // Agent is inactive → activate
            $update_stmt = $conn->prepare("UPDATE agents SET agentStatus = 1, updated_at = ? WHERE agentId = ?");
            $update_stmt->bind_param("si", $current_time, $agent_id);
            $update_stmt->execute();

            header("Location: agentlist.php?status=activated");
            exit;
        }
    } else {
        // Agent not found
        header("Location: agentlist.php?status=notfound");
        exit;
    }
} else {
    // Invalid request
    header("Location: agentlist.php?status=invalid");
    exit;
}
