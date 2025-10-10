<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

http_response_code(404);
$page = '404';

include 'includes/header.php';
?>

<section class="pt-32 pb-20 min-h-screen flex items-center">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="fade-in">
            <div class="mb-8">
                <i class="fas fa-exclamation-triangle text-8xl text-olive-600 mb-6"></i>
            </div>
            
            <h1 class="text-6xl md:text-8xl font-bold text-gray-900 mb-6">404</h1>
            
            <h2 class="text-2xl md:text-3xl font-bold text-gray-700 mb-6">
                <?php echo e(t('error_404_heading')); ?>
            </h2>
            
            <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                <?php echo e(t('error_404_text')); ?>
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                   class="btn-primary inline-flex items-center">
                    <i class="fas fa-home mr-2"></i>
                    <?php echo e(t('error_404_home_button')); ?>
                </a>
                
                <a href="contacts.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                   class="btn-secondary inline-flex items-center">
                    <i class="fas fa-phone mr-2"></i>
                    <?php echo e(t('contact_us')); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>