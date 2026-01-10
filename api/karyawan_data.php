<?php
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../models/PointModel.php';
require_once '../models/BathroomModel.php';

if (!Auth::isLoggedIn() || !Auth::hasRole('karyawan')) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user = Auth::getUser();
$pointModel = new PointModel();
$bathroomModel = new BathroomModel();

$points = $pointModel->getUserPoints($user['id']);
$recent_transactions = $pointModel->getRecentTransactions($user['id'], 5);
$cleaning_stats = $pointModel->getCleaningStats($user['id']);

echo json_encode([
    'success' => true,
    'points' => $points,
    'recent_transactions' => $recent_transactions,
    'cleaning_stats' => $cleaning_stats,
    'user' => $user,
    'timestamp' => time()
]);
?>