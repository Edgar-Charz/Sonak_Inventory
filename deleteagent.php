<?php
include 'includes/db_connection.php';
include 'includes/session.php';

if (isset($_GET['id'])) {
    $agent_id = intval($_GET['id']);

    // Deleting agent
    $delete_agent_query = $conn->prepare("DELETE FROM agents WHERE agentId = ?");
    $delete_agent_query->bind_param("i", $agent_id);

    if ($delete_agent_query->execute()) {
        header("Location: agentlist.php?status=success");
        exit;
    } else {
        header("Location: agentlist.php?status=error");
        exit;
    }
} else {
    header("Location: agentlist.php?status=error");
}
exit; 
