<?php
// motivation_letter_delete.php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    exit('Invalid ID');
}

$sql = "DELETE FROM motivation_letters WHERE id = $1";
$result = pg_query_params($conn, $sql, [$id]);

if (!$result) {
    http_response_code(500);
    exit('Failed to delete motivation letter');
}

// redirect balik ke list
header('Location: motivation_letter_list.php?deleted=1');
exit;
