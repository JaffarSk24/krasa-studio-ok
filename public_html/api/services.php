
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
        SELECT s.*, 
               sc.name_sk as category_name_sk, 
               sc.name_ru as category_name_ru, 
               sc.name_ua as category_name_ua,
               sc.slug as category_slug
        FROM services s 
        JOIN service_categories sc ON s.category_id = sc.id 
        WHERE s.is_active = 1 AND sc.is_active = 1 
        ORDER BY sc.order_num, s.order_num
    ");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add localized fields for current language
    foreach ($services as &$service) {
        $service['name'] = getLocalizedField($service, 'name');
        $service['description'] = getLocalizedField($service, 'description');
        $service['category_name'] = getLocalizedField($service, 'category_name');
    }
    
    echo json_encode($services);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load services'
    ]);
}
?>
