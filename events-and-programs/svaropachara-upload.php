<?php
/**
 * Svaropachara flyer upload API
 * Protects uploads with a simple password (change SVAROPACHARA_UPLOAD_PASSWORD below).
 */
header('Content-Type: application/json; charset=utf-8');

const SVAROPACHARA_UPLOAD_PASSWORD = 'sschrc-svaropachara-2026'; // change this before going live

function respond($ok, $message, $extra = []) {
    http_response_code($ok ? 200 : 400);
    echo json_encode(array_merge(['ok' => $ok, 'message' => $message], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Only POST uploads are allowed.');
}

$password = isset($_POST['password']) ? trim((string)$_POST['password']) : '';
if (!hash_equals(SVAROPACHARA_UPLOAD_PASSWORD, $password)) {
    respond(false, 'Incorrect upload password.');
}

$title = trim((string)($_POST['title'] ?? ''));
$date = trim((string)($_POST['date'] ?? ''));
$time = trim((string)($_POST['time'] ?? ''));
$venue = trim((string)($_POST['venue'] ?? ''));
$status = strtolower(trim((string)($_POST['status'] ?? 'upcoming')));
$organizedBy = trim((string)($_POST['organizedBy'] ?? 'Sri Shankara Cancer Hospital & Research Centre, Bangalore'));
$association = trim((string)($_POST['association'] ?? ''));

if ($title === '' || $date === '') {
    respond(false, 'Title and date are required.');
}
if (!in_array($status, ['upcoming', 'completed'], true)) {
    respond(false, 'Status must be upcoming or completed.');
}
if (!isset($_FILES['flyer']) || !is_uploaded_file($_FILES['flyer']['tmp_name'])) {
    respond(false, 'Please choose a flyer file to upload.');
}

$file = $_FILES['flyer'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    respond(false, 'File upload failed. Please try again.');
}
if (($file['size'] ?? 0) > 12 * 1024 * 1024) {
    respond(false, 'Flyer must be 12MB or smaller.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
];
if (!isset($allowed[$mime])) {
    respond(false, 'Only JPG, PNG, WEBP, or PDF flyers are allowed.');
}

$ext = $allowed[$mime];
$slugBase = preg_replace('/[^a-z0-9]+/i', '-', strtolower($title));
$slugBase = trim($slugBase, '-');
if ($slugBase === '') {
    $slugBase = 'svaropachara-flyer';
}
$id = $slugBase . '-' . preg_replace('/[^0-9]/', '', $date) . '-' . substr(uniqid('', true), -5);
$filename = $id . '.' . $ext;

$folder = __DIR__ . '/assets/svaropachara/flyers/' . $status;
if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
    respond(false, 'Could not create flyer folder.');
}

$dest = $folder . DIRECTORY_SEPARATOR . $filename;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    respond(false, 'Could not save the uploaded flyer.');
}

$jsonPath = __DIR__ . '/svaropachara-events.json';
$data = ['upcoming' => [], 'completed' => []];
if (is_file($jsonPath)) {
    $raw = file_get_contents($jsonPath);
    $parsed = json_decode($raw, true);
    if (is_array($parsed)) {
        $data['upcoming'] = isset($parsed['upcoming']) && is_array($parsed['upcoming']) ? $parsed['upcoming'] : [];
        $data['completed'] = isset($parsed['completed']) && is_array($parsed['completed']) ? $parsed['completed'] : [];
    }
}

$dateLabel = date('l, jS F Y', strtotime($date));
$entry = [
    'id' => $id,
    'title' => $title,
    'date' => $date,
    'dateLabel' => $dateLabel,
    'time' => $time,
    'venue' => $venue,
    'flyer' => 'assets/svaropachara/flyers/' . $status . '/' . $filename,
    'organizedBy' => $organizedBy,
    'association' => $association,
];

array_unshift($data[$status], $entry);

$encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($encoded === false || file_put_contents($jsonPath, $encoded . PHP_EOL) === false) {
    respond(false, 'Flyer saved, but failed to update the Svaropachara events list.');
}

respond(true, 'Flyer uploaded successfully.', [
    'entry' => $entry,
    'redirect' => 'svaropachara.html#' . $status,
]);
