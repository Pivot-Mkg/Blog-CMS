<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$pdo = get_pdo();
$upload = upload_image($_FILES['file'], $pdo);
if (!$upload) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file']);
    exit;
}

echo json_encode(['location' => $upload['url']]);
