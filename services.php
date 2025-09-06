
<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$page = 'services';

// Load services from database
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

// Group services by category
$servicesByCategory = [];
foreach ($services as $service) {
    $categoryId = $service['category_id'];
    if (!isset($servicesByCategory[$categoryId])) {
        $servicesByCategory[$categoryId] = [
            'category' => $service,
            'services' => []
        ];
    }
    $servicesByCategory[$categoryId]['services'][] = $service;
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="pt-32 pb-20 bg-gradient-to-br from-olive-50 to-green-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                <?php echo e(t('services_title')); ?>
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                <?php echo e(t('services_subtitle')); ?>
            </p>
        </div>
    </div>
</section>

<!-- Services List -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($servicesByCategory)): ?>
            <div class="space-y-6 fade-in">
                <?php foreach ($servicesByCategory as $categoryData): ?>
                    <?php 
                    $category = $categoryData['category'];
                    $categoryServices = $categoryData['services'];
                    $categoryName = getLocalizedField($category, 'category_name');
                    ?>
                    
                    <div class="accordion-item">
                        <!-- Category Header -->
                        <div class="accordion-header">
                            <div class="flex items-center">
                                <i class="fas fa-spa text-olive-600 mr-3 text-xl"></i>
                                <h2 class="text-2xl font-bold text-gray-900">
                                    <?php echo e($categoryName); ?>
                                </h2>
                            </div>
                            <i class="fas fa-chevron-down accordion-icon text-olive-600 transition-transform duration-300"></i>
                        </div>
                        
                        <!-- Category Content -->
                        <div class="accordion-content">
                            <div class="accordion-body">
                                <?php if (!empty($categoryServices)): ?>
                                    <div class="grid gap-6">
                                        <?php foreach ($categoryServices as $service): ?>
                                            <div class="bg-gray-50 rounded-lg p-6 service-card">
                                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                    <div class="flex-1">
                                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                                            <?php echo e(getLocalizedField($service, 'name')); ?>
                                                        </h3>
                                                        
                                                        <?php $description = getLocalizedField($service, 'description'); ?>
                                                        <?php if ($description): ?>
                                                            <p class="text-gray-600 mb-3">
                                                                <?php echo e($description); ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        
                                                        <div class="flex items-center gap-4 text-sm text-gray-500">
                                                            <div class="flex items-center">
                                                                <i class="fas fa-clock mr-1"></i>
                                                                <?php echo formatTime($service['duration']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                                        <div class="text-right">
                                                            <div class="text-2xl font-bold text-olive-600">
                                                                <?php echo formatPrice($service['price']); ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="flex gap-2">
                                                            <?php 
                                                            $whatsappNumber = $service['whatsapp_number'] ?: '+421905123456';
                                                            $serviceName = getLocalizedField($service, 'name');
                                                            $whatsappMessage = urlencode(
                                                                (CURRENT_LANG === 'sk' ? "Zdravím! Chcel by som si rezervovať termín na službu: " : 
                                                                (CURRENT_LANG === 'ru' ? "Здравствуйте! Хотел бы записаться на услугу: " : 
                                                                "Здравствуйте! Хотів би записатися на послугу: ")) . $serviceName
                                                            );
                                                            ?>
                                                            
                                                            <a href="https://wa.me/<?php echo str_replace('+', '', $whatsappNumber); ?>?text=<?php echo $whatsappMessage; ?>" 
                                                               target="_blank" 
                                                               rel="noopener noreferrer"
                                                               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center">
                                                                <i class="fab fa-whatsapp mr-1"></i>
                                                                <span class="hidden sm:inline">WhatsApp</span>
                                                            </a>
                                                            
                                                            <button onclick="window.location.href='index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>#booking'" 
                                                                    class="btn-primary px-4 py-2 flex items-center">
                                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                                <span class="hidden sm:inline"><?php echo e(t('book_service')); ?></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <i class="fas fa-spa text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-500 mb-4">
                    <?php echo CURRENT_LANG === 'sk' ? 'Služby sa načítavajú...' : (CURRENT_LANG === 'ru' ? 'Услуги загружаются...' : 'Послуги завантажуються...'); ?>
                </h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-olive-600 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                <?php echo CURRENT_LANG === 'sk' ? 'Rezervujte si svoj termín už dnes!' : 
                          (CURRENT_LANG === 'ru' ? 'Забронируйте время уже сегодня!' : 
                           'Забронюйте свій час вже сьогодні!'); ?>
            </h2>
            <p class="text-xl text-olive-100 mb-8 max-w-3xl mx-auto">
                <?php echo CURRENT_LANG === 'sk' ? 'Naši profesionáli sa tešia na stretnutie s vami.' : 
                          (CURRENT_LANG === 'ru' ? 'Наши профессионалы с нетерпением ждут встречи с вами.' : 
                           'Наші професіонали з нетерпінням чекають зустрічі з вами.'); ?>
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.location.href='index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>#booking'" 
                        class="bg-white text-olive-600 hover:bg-gray-100 px-8 py-4 rounded-lg font-semibold transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php echo e(t('book_now')); ?>
                </button>
                
                <a href="contacts.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                   class="border-2 border-white text-white hover:bg-white hover:text-olive-600 px-8 py-4 rounded-lg font-semibold transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-phone mr-2"></i>
                    <?php echo e(t('contact_us')); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
