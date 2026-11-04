<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use Cloudinary\Cloudinary;

header('Content-Type: application/json; charset=utf-8');

function reponse(int $code, bool $success, string $message): void {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reponse(405, false, 'Méthode non autorisée');
}

$nom    = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$ville  = trim($_POST['ville'] ?? '');

if ($nom === '' || $prenom === '' || $ville === '') {
    reponse(400, false, 'Tous les champs sont obligatoires');
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    reponse(400, false, 'Photo manquante ou invalide');
}

$tmp  = $_FILES['photo']['tmp_name'];
$mime = mime_content_type($tmp);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    reponse(400, false, 'Format non accepté (jpg, png, webp)');
}
if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
    reponse(400, false, 'Image trop lourde (max 5 Mo)');
}

try {
    $cloudinary = new Cloudinary(getenv('CLOUDINARY_URL'));
    $upload = $cloudinary->uploadApi()->upload($tmp, ['folder' => 'stagiaires']);
    $photoUrl = $upload['secure_url'];

    $pdo = getDatabase();
    $stmt = $pdo->prepare(
        'INSERT INTO stagiaires (nom, prenom, ville, photo) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$nom, $prenom, $ville, $photoUrl]);

    reponse(201, true, 'Stagiaire inscrit avec succès');
} catch (Throwable $e) {
    error_log($e->getMessage());
    reponse(500, false, 'Erreur serveur');
}