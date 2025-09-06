
<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    
    $stmt = $conn->prepare("
        SELECT id, filename, 
               alt_sk, alt_ru, alt_ua,
               description_sk, description_ru, description_ua,
               created_at
        FROM gallery_images 
        WHERE is_active = 1 
        ORDER BY order_num, created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add localized fields
    foreach ($images as &$image) {
        $image['alt'] = getLocalizedField($image, 'alt') ?: 'Krása štúdio OK';
        $image['description'] = getLocalizedField($image, 'description');
        $image['url'] = 'assets/images/' . $image['filename'];
    }
    
    echo json_encode($images);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load gallery'
    ]);
}
?>
