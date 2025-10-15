<?php

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$page = 'pricing';

// Load services from database
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT s.*, 
           sc.name_sk as category_name_sk, 
           sc.name_ru as category_name_ru, 
           sc.name_ua as category_name_ua,
           sc.order_num as category_order
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
                <?php echo e(t('pricing_title')); ?>
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                <?php echo e(t('pricing_subtitle')); ?>
            </p>
        </div>
    </div>
</section>

<!-- Pricing Content (SEO Optimized) -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($servicesByCategory)): ?>
            <div class="space-y-16">
                <?php foreach ($servicesByCategory as $categoryData): ?>
                    <?php 
                    $category = $categoryData['category'];
                    $categoryServices = $categoryData['services'];
                    $categoryName = getLocalizedField($category, 'category_name');
                    ?>
                    
                    <div class="fade-in">
                        <!-- Category Header -->
                        <div class="text-center mb-12">
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                                <?php echo e($categoryName); ?>
                            </h2>
                            <div class="w-24 h-1 bg-olive-600 mx-auto"></div>
                        </div>
                        
                        <!-- Services Grid -->
                        <?php if (!empty($categoryServices)): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                <?php foreach ($categoryServices as $service): ?>
                                    <?php
                                    $serviceName = getLocalizedField($service, 'name');
                                    $prefillTarget = CURRENT_LANG !== DEFAULT_LANGUAGE ? 'index.php?lang=' . CURRENT_LANG . '#booking' : 'index.php#booking';

                                    // digits-only номер из config (через WHATSAPP_NUMBER)
                                    $waDigits = getWhatsappNumber(true);

                                    // Формируем текст "Категория: Услуга" — buildWhatsappMessage возвращает urlencode()
                                    $waText = buildWhatsappMessage($categoryName, $serviceName, CURRENT_LANG);

                                    $whatsappLink = 'https://wa.me/' . $waDigits . '?text=' . $waText;
                                    ?>
                                    <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-lg card-hover">
                                        <div class="text-center">
                                            <!-- Service Icon -->
                                            <div class="w-16 h-16 bg-olive-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                                <i class="fas fa-spa text-olive-600 text-2xl"></i>
                                            </div>
                                            
                                            <!-- Service Name -->
                                            <h3 class="text-xl font-bold text-gray-900 mb-3">
                                                <?php echo e($serviceName); ?>
                                            </h3>
                                            
                                            <!-- Service Description -->
                                            <?php $description = getLocalizedField($service, 'description'); ?>
                                            <?php if ($description): ?>
                                                <p class="text-gray-600 mb-4 text-sm leading-relaxed">
                                                    <?php echo e($description); ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <!-- Duration -->
                                            <div class="flex items-center justify-center mb-6 text-gray-500">
                                                <i class="fas fa-clock mr-2"></i>
                                                <span><?php echo formatTime($service['duration']); ?></span>
                                            </div>
                                            
                                            <!-- Price -->
                                            <div class="mb-6">
                                                <div class="text-3xl font-bold text-olive-600 mb-2">
                                                    <?php echo formatPrice($service['price']); ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Book Button -->
                                            <button
                                                type="button"
                                                class="w-full btn-primary mb-3 prefill-booking-btn"
                                                data-category-id="<?php echo $service['category_id']; ?>"
                                                data-service-id="<?php echo $service['id']; ?>"
                                                data-category-name="<?php echo e($categoryName); ?>"
                                                data-service-name="<?php echo e($serviceName); ?>"
                                                data-lang="<?php echo CURRENT_LANG; ?>"
                                                data-default-lang="<?php echo DEFAULT_LANGUAGE; ?>"
                                                data-target="<?php echo $prefillTarget; ?>">
                                                <?php echo e(t('book_service')); ?>
                                            </button>
                                            
                                            <!-- WhatsApp Button -->
                                            <a href="<?php echo e($whatsappLink); ?>"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                                                <i class="fab fa-whatsapp mr-2"></i>
                                                WhatsApp
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- SEO Text Content for Category -->
                        <div class="mt-12 prose prose-lg max-w-none">
                            <?php if (stripos($categoryName, 'kadernícke') !== false || stripos($categoryName, 'vlasy') !== false || stripos($categoryName, 'парикмахер') !== false || stripos($categoryName, 'волос') !== false || stripos($categoryName, 'перукар') !== false || stripos($categoryName, 'волосс') !== false): ?>
                                <p><?php echo e(t('pricing_category_hair_text')); ?></p>
                            <?php elseif (stripos($categoryName, 'kozmetika') !== false || stripos($categoryName, 'ošetrenie') !== false || stripos($categoryName, 'косметолог') !== false || stripos($categoryName, 'процедур') !== false): ?>
                                <p><?php echo e(t('pricing_category_cosmetics_text')); ?></p>
                            <?php elseif (stripos($categoryName, 'manikúra') !== false || stripos($categoryName, 'nechty') !== false || stripos($categoryName, 'маникюр') !== false || stripos($categoryName, 'ногт') !== false || stripos($categoryName, 'манікюр') !== false || stripos($categoryName, 'нігт') !== false): ?>
                                <p><?php echo e(t('pricing_category_manicure_text')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <i class="fas fa-euro-sign text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-500 mb-4">
                    <?php echo e(t('pricing_loading_title')); ?>
                </h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Additional SEO Content (styled) -->
<section class="py-20 bg-gradient-to-br from-olive-50 to-green-50">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Intro -->
    <div class="text-center mb-12 fade-in">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
        <?php echo e(t('pricing_seo_intro_title')); ?>
      </h2>
      <p class="text-lg md:text-xl text-gray-700 max-w-3xl mx-auto">
        <?php echo e(t('pricing_seo_intro_text')); ?>
      </p>
    </div>

    <!-- 3 feature cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 fade-in">
      <!-- Pricing transparency -->
      <div class="bg-white rounded-2xl p-8 shadow-lg card-hover h-full">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-full bg-olive-600/10 flex items-center justify-center">
            <i class="fas fa-list text-olive-700 text-xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900">
            <?php echo e(t('pricing_seo_pricing_title')); ?>
          </h3>
        </div>
        <p class="text-gray-600">
          <?php echo e(t('pricing_seo_pricing_text')); ?>
        </p>
      </div>

      <!-- Products and tech -->
      <div class="bg-white rounded-2xl p-8 shadow-lg card-hover h-full">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-full bg-olive-600/10 flex items-center justify-center">
            <i class="fas fa-flask text-olive-700 text-xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900">
            <?php echo e(t('pricing_seo_products_title')); ?>
          </h3>
        </div>
        <p class="text-gray-600">
          <?php echo e(t('pricing_seo_products_text')); ?>
        </p>
      </div>

      <!-- Booking/WhatsApp -->
      <div class="bg-white rounded-2xl p-8 shadow-lg card-hover h-full flex flex-col">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-full bg-olive-600/10 flex items-center justify-center">
            <i class="fas fa-calendar-check text-olive-700 text-xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900">
            <?php echo e(t('pricing_seo_booking_title')); ?>
          </h3>
        </div>

        <p class="text-gray-600 mb-6">
          <?php echo e(t('pricing_seo_booking_text')); ?>
        </p>

        <?php
        // Получаем номер WhatsApp в цифровом формате безопасно:
        if (function_exists('getWhatsappNumber')) {
            $waPhone = getWhatsappNumber(true);
        } elseif (defined('WA_PHONE_INTL')) {
            $waPhone = WA_PHONE_INTL;
        } else {
            $waPhone = '';
        }
        $waPhone = is_scalar($waPhone) ? $waPhone : '';
        ?>
  </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-olive-600 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                <?php echo e(t('pricing_cta_title')); ?>
            </h2>
            <p class="text-xl text-olive-100 mb-8 max-w-3xl mx-auto">
                <?php echo e(t('pricing_cta_text')); ?>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.prefill-booking-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const payload = {
                categoryId: button.dataset.categoryId,
                serviceId: button.dataset.serviceId,
                categoryName: button.dataset.categoryName,
                serviceName: button.dataset.serviceName,
                lang: button.dataset.lang
            };
            sessionStorage.setItem('bookingPrefill', JSON.stringify(payload));
            window.location.href = button.dataset.target;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>