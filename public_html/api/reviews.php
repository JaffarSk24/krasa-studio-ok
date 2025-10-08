
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
    
    $stmt = $conn->prepare("
        SELECT id, client_name, rating, 
               text_sk, text_ru, text_ua,
               created_at
        FROM reviews 
        WHERE is_active = 1 
        ORDER BY order_num, created_at DESC
    ");
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add localized text field
    foreach ($reviews as &$review) {
        $review['text'] = getLocalizedField($review, 'text');
    }
    
    echo json_encode($reviews);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load reviews'
    ]);
}
?>
