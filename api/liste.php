<?php
require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDatabase();
    $stmt = $pdo->query('SELECT id, nom, prenom, ville, photo, date_inscription FROM stagiaires ORDER BY id DESC');
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}