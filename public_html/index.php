<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$page = 'home';

// Generate time slots for the next 30 days if they don't exist
$db = new Database();
$conn = $db->getConnection();

// Check if we have time slots
$stmt = $conn->prepare("SELECT COUNT(*) FROM time_slots WHERE date >= CURDATE()");
$stmt->execute();
$slotsCount = $stmt->fetchColumn();

if ($slotsCount < 10) {
    generateTimeSlots(date('Y-m-d'), date('Y-m-d', strtotime('+30 days')));
}

// Load services for booking form
$stmt = $conn->prepare("
    SELECT s.*, sc.name_sk as category_name_sk, sc.name_ru as category_name_ru, sc.name_ua as category_name_ua 
    FROM services s 
    JOIN service_categories sc ON s.category_id = sc.id 
    WHERE s.is_active = 1 AND sc.is_active = 1 
    ORDER BY sc.order_num, s.order_num
");
$stmt->execute();
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load gallery images for preview (first 6)
$stmt = $conn->prepare("SELECT * FROM gallery_images WHERE is_active = 1 ORDER BY order_num, created_at DESC LIMIT 6");
$stmt->execute();
$galleryImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0 z-0">
        <div class="relative w-full h-full">
            <img src="assets/images/8.webp" alt="<?php echo e(t('hero_title')); ?>" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-black/30"></div>
        </div>
    </div>

    <!-- Content -->
    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <div class="space-y-8 fade-in">
            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <div class="relative w-24 h-24 md:w-32 md:h-32">
                    <img src="assets/images/Mini Логотип без фона.png" alt="Krása štúdio OK" class="w-full h-full object-contain drop-shadow-2xl">
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold leading-tight">
                <?php echo e(t('hero_title')); ?>
            </h1>

            <!-- Subtitle -->
            <p class="text-xl md:text-2xl lg:text-3xl text-gray-200 max-w-4xl mx-auto leading-relaxed">
                <?php echo e(t('hero_subtitle')); ?>
            </p>

            <!-- Description -->
            <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto">
                <?php echo e(t('hero_description')); ?>
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-12">
                <button onclick="scrollToBooking()" class="bg-olive-600 hover:bg-olive-700 text-white px-8 py-4 text-lg rounded-full shadow-2xl transform hover:scale-105 transition-all duration-300 flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php echo e(t('book_now')); ?>
                </button>

                <a href="https://wa.me/<?php echo getWhatsappNumber(true); ?>?text=<?php echo buildWhatsappMessage(); ?>"
                   target="_blank" rel="noopener noreferrer"
                   class="bg-green-600 hover:bg-green-700 border-green-600 text-white px-8 py-4 text-lg rounded-full shadow-2xl transform hover:scale-105 transition-all.duration-300 flex items-center js-whatsapp-link"
                   data-whatsapp-number="<?php echo getWhatsappNumber(true); ?>"
                   data-service-name="">
                    <i class="fab fa-whatsapp mr-2"></i>
                    WhatsApp
                </a>
            </div>

            <!-- Notino Booking Link -->
            <div class="pt-8">
                <p class="text-gray-300 mb-4">
                    <?php echo e(t('notino_booking')); ?>
                </p>
                <a href="https://www.notino.sk/salony/krasa-studio-ok/" target="_blank" rel="noopener noreferrer"
                   class="text-white border border-white/30 hover:bg-white/10 px-6 py-2 rounded-full inline-block transition-colors duration-300">
                    <?php echo e(t('book_via_notino')); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
        <div class="flex flex-col items-center text-white/70">
            <div class="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center animate-bounce">
                <div class="w-1 h-3 bg-white/60 rounded-full mt-2"></div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Section -->
<section id="booking" class="py-20 bg-gradient-to-br from-green-50 to-olive-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                <?php echo e(t('booking_title')); ?>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                <?php echo e(t('booking_confirmation')); ?>
            </p>
        </div>

        <div class="max-w-2xl mx-auto fade-in booking-content">
            <div class="bg-white/80 backdrop-blur-sm shadow-2xl border-olive-200 border rounded-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-olive-50 px-8 py-6 border-b border-olive-200">
                    <h3 class="text-2xl font-bold text-center text-olive-700">
                        <?php echo e(t('reservation_form')); ?>
                    </h3>
                </div>
                
                <!-- Form -->
                <div class="p-8">
                    <form id="booking-form" class="space-y-6" method="POST" action="booking.php">
                        <!-- Category -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <i class="fas fa-list text-olive-600"></i>
                                <?php echo e(t('select_category')); ?>
                            </label>
                            <select id="service-category" name="category_id" required class="form-control border-olive-200 focus:ring-olive-600">
                                <option value=""><?php echo e(t('select_category')); ?></option>
                            </select>
                        </div>

                        <!-- Service -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <i class="fas fa-spa text-olive-600"></i>
                                <?php echo e(t('select_service')); ?>
                            </label>
                            <select id="service-id" name="service_id" required class="form-control border-olive-200 focus:ring-olive-600">
                                <option value=""><?php echo e(t('select_service')); ?></option>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-olive-600"></i>
                                <?php echo e(t('select_date')); ?>
                            </label>
                            <input type="text" id="booking-date" name="date" required
                                   class="form-control border-olive-200 focus:ring-olive-600"
                                   placeholder="<?php echo e(t('select_date')); ?>" readonly>
                        </div>

                        <!-- Time -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <i class="fas fa-clock text-olive-600"></i>
                                <?php echo e(t('select_time')); ?>
                            </label>
                            <select id="booking-time" name="time" required class="form-control border-olive-200 focus:ring-olive-600">
                                <option value=""><?php echo e(t('select_time')); ?></option>
                            </select>
                        </div>

                        <!-- Name -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <i class="fas fa-user text-olive-600"></i>
                                <?php echo e(t('name')); ?>
                            </label>
                            <input type="text" id="name" name="name" required
                                   class="form-control border-olive-200 focus:ring-olive-600"
                                   placeholder="<?php echo e(t('name')); ?>">
                        </div>

                        <!-- Phone -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                <i class="fas fa-phone text-olive-600"></i>
                                <?php echo e(t('phone_number')); ?>
                            </label>
                            <input type="tel" name="phone" required 
                                   class="form-control border-olive-200 focus:ring-olive-600"
                                   placeholder="+421 905 123 456">
                        </div>

                        <!-- Message -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">
                                <?php echo e(t('message')); ?> (<?php echo e(t('optional')); ?>)
                            </label>
                            <textarea name="message" rows="3" 
                                      class="form-control border-olive-200 focus:ring-olive-600"
                                      placeholder="<?php echo e(t('message')); ?>..."></textarea>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="w-full btn-primary py-3 text-lg">
                            <?php echo e(t('submit')); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Google Reviews Section -->
<section id="google-reviews-section" class="py-20 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
        <?php echo e(t('reviews_title')); ?>
      </h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
        <?php echo e(t('reviews_description')); ?>
      </p>
    </div>

    <div class="flex justify-end mb-8">
      <a href="https://g.page/r/CXRvJt2HfXWbEBM/review" target="_blank" rel="noopener noreferrer"
         class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-olive-600 text-white hover:bg-olive-700 transition">
        <i class="fas fa-pen"></i>
        <?php echo t('leave_review_button'); ?>
      </a>
    </div>

    <!-- Только отзывы в одну строку -->
    <div id="google-reviews" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
      <div id="google-reviews-placeholder" class="col-span-full text-center text-gray-400 py-8">
        <?php echo t('loading_reviews'); ?>
      </div>
    </div>
</section>

<!-- Gallery Preview -->
<?php if (!empty($galleryImages)): ?>
<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                <?php echo e(t('gallery_title')); ?>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                <?php echo e(t('gallery_subtitle')); ?>
            </p>
        </div>

        <div class="gallery-grid mb-12 fade-in">
            <?php foreach ($galleryImages as $image): ?>
            <div class="gallery-item">
                <img src="assets/images/<?php echo e($image['filename']); ?>" 
                     alt="<?php echo e(getLocalizedField($image, 'alt')); ?>" 
                     class="w-full h-full object-cover">
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center fade-in">
            <a href="gallery.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
               class="btn-primary inline-flex items-center">
                <?php echo e(t('view_all')); ?>
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Contact Section -->
<section class="py-20 bg-olive-600 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
            <!-- Left side - Info -->
            <div class="fade-in">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">
                    <?php echo e(t('get_in_touch')); ?>
                </h2>
                <p class="text-xl mb-8 text-olive-100">
                    <?php echo e(t('contact_form_description')); ?>
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i class="fas fa-phone text-2xl mr-4"></i>
                        <a href="tel:+421915310337" class="text-lg hover:underline">
                            +421 915 310 337
                        </a>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope text-2xl mr-4"></i>
                        <a href="mailto:info@krasastudio.sk" class="text-lg hover:underline">
                            info@krasastudio.sk
                        </a>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-2xl mr-4"></i>
                        <a href="https://maps.app.goo.gl/eaVEMGG5NqS1wSJf6" target="_blank" rel="noopener" class="text-lg hover:underline">
                            Tomášikova 11, Bratislava
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right side - Contact Form -->
            <div class="fade-in">
                <form id="contact-form" class="bg-white text-gray-900 p-8 rounded-2xl shadow-2xl">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2"><?php echo e(t('name')); ?></label>
                            <input type="text" name="name" required class="form-control">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2"><?php echo e(t('phone_number')); ?></label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2"><?php echo e(t('email')); ?></label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2"><?php echo e(t('message')); ?></label>
                            <textarea name="message" rows="4" required class="form-control"></textarea>
                        </div>
                        
                        <button type="submit" class="w-full btn-primary">
                            <?php echo e(t('submit')); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- WhatsApp Float Button -->
<a href="#"
   target="_blank" rel="noopener noreferrer"
   class="whatsapp-float js-whatsapp-link js-whatsapp-short"
   data-whatsapp-number="<?php echo getWhatsappNumber(true); ?>"
   data-whatsapp-variant="short"
   data-service-name="">
    <i class="fab fa-whatsapp"></i>
</a>

<?php include 'includes/footer.php'; ?>