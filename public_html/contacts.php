<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$page = 'contacts';

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="pt-32 pb-20 bg-gradient-to-br from-olive-50 to-green-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                <?php echo e(t('contacts')); ?>
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                <?php echo e(t('get_in_touch')); ?>
            </p>
        </div>
    </div>
</section>

<!-- Contact Information & Map -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Left side - Contact Info -->
            <div class="fade-in">
                <h2 class="text-3xl.font-bold text-gray-900 mb-8">
                    <?php echo e(t('contact_info')); ?>
                </h2>
                
                <!-- Contact Details -->
                <div class="space-y-6 mb-8">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-olive-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-phone text-olive-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">
                                <?php echo e(t('phone_number')); ?>
                            </h3>
                            <a href="tel:+421915310337" class="text-gray-600 hover:underline">+421 915 310 337</a>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-olive-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-envelope text-olive-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1"><?php echo e(t('email')); ?></h3>
                            <a href="mailto:info@krasastudio.sk" class="text-gray-600 hover:underline">info@krasastudio.sk</a>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-olive-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-olive-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">
                                <?php echo e(t('address')); ?>
                            </h3>
                            <a href="https://maps.app.goo.gl/eaVEMGG5NqS1wSJf6" target="_blank" rel="noopener" class="text-gray-600 hover:underline">
                                Tomášikova 11, Bratislava
                            </a>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-olive-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-clock text-olive-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">
                                <?php echo e(t('opening_hours')); ?>
                            </h3>
                            <div class="text-gray-600">
                                <p><?php echo t('hours_schedule'); ?></p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php echo e(t('weekend_closed_note')); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Links & WhatsApp -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-900"><?php echo e(t('follow_us')); ?></h3>
                    <div class="flex gap-4 flex-wrap">
                        <a href="https://www.facebook.com/Krasa.Studio.OK.Bratislava"
                           class="bg-olive-100 hover:bg-olive-200 text-olive-600 hover:text-olive-700 px-6 py-3 rounded-lg transition-colors duration-200 flex items-center">
                            <i class="fab fa-facebook mr-2"></i>
                            Facebook
                        </a>

                        <a href="https://www.instagram.com/olena.krasastudio/"
                           class="bg-olive-100 hover:bg-olive-200 text-olive-600 hover:text-olive-700 px-6 py-3 rounded-lg transition-colors duration-200 flex items-center">
                            <i class="fab fa-instagram mr-2"></i>
                            Instagram
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Google Map -->
            <div class="fade-in">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">
                    <?php echo e(t('map_section_title')); ?>
                </h2>
                
                <div class="bg-gray-200 rounded-2xl overflow-hidden shadow-lg aspect-video">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2661.645323358107!2d17.16043657652402!3d48.1556426499032!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x476c8fa5863f1251%3A0x9b757d87dd266f74!2zT0sgxaF0w7pkaW8!5e0!3m2!1ssk!2ssk!4v1757076525477!5m2!1ssk!2ssk" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                
                <div class="mt-6 p-6 bg-olive-50 rounded-xl">
                    <h4 class="font-semibold text-gray-900 mb-2">
                        <?php echo e(t('how_to_get_title')); ?>
                    </h4>
                    <p class="text-gray-600">
                        <?php echo e(t('how_to_get_desc')); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?php echo e(t('contact_form_title')); ?>
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                <?php echo e(t('contact_form_description')); ?>
            </p>
        </div>
        
        <div class="max-w-2xl mx-auto fade-in">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <form id="contact-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo e(t('name')); ?> *
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required 
                                   class="form-control">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo e(t('phone_number')); ?>
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="+421 915 310 337"
                                   class="form-control">
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(t('email')); ?>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(t('message')); ?> *
                        </label>
                        <textarea id="message" 
                                  name="message" 
                                  rows="6" 
                                  required 
                                  placeholder="<?php echo e(t('contact_message_placeholder')); ?>"
                                  class="form-control"></textarea>
                    </div>
                    
                    <div class="text-sm text-gray-500">
                        * <?php echo e(t('required_fields_note')); ?>
                    </div>
                    
                    <button type="submit" class="w-full btn-primary py-4 text-lg">
                        <i class="fas fa-paper-plane mr-2"></i>
                        <?php echo e(t('submit')); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?php echo e(t('faq_title')); ?>
            </h2>
        </div>
        
        <div class="space-y-4 fade-in">
            <!-- FAQ Item 1 -->
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="font-semibold text-gray-900">
                        <?php echo e(t('faq_how_to_book_question')); ?>
                    </h3>
                    <i class="fas fa-chevron-down accordion-icon text-olive-600"></i>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p class="text-gray-600">
                            <?php echo e(t('faq_how_to_book_answer')); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 2 -->
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="font-semibold text-gray-900">
                        <?php echo e(t('faq_opening_hours_question')); ?>
                    </h3>
                    <i class="fas fa-chevron-down accordion-icon text-olive-600"></i>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p class="text-gray-600">
                            <?php echo e(t('faq_opening_hours_answer')); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 3 -->
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="font-semibold text-gray-900">
                        <?php echo e(t('faq_reschedule_question')); ?>
                    </h3>
                    <i class="fas fa-chevron-down accordion-icon text-olive-600"></i>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p class="text-gray-600">
                            <?php echo e(t('faq_reschedule_answer')); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 4 -->
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="font-semibold text-gray-900">
                        <?php echo e(t('faq_payment_methods_question')); ?>
                    </h3>
                    <i class="fas fa-chevron-down accordion-icon text-olive-600"></i>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p class="text-gray-600">
                            <?php echo e(t('faq_payment_methods_answer')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Set reCAPTCHA site key for contact form
recaptchaSiteKey = '<?php echo RECAPTCHA_SITE_KEY; ?>';
</script>

<?php include 'includes/footer.php'; ?>