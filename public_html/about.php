<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$page = 'about';

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="pt-32 pb-20 bg-gradient-to-br from-olive-50 to-green-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                <?php echo e(t('about_title')); ?>
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                <?php echo e(t('about_subtitle')); ?>
            </p>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="fade-in">
                <div class="relative aspect-square rounded-2xl overflow-hidden shadow-2xl">
                    <img src="assets/images/5.webp" 
                         alt="<?php echo e(t('about_title')); ?>" 
                         class="w-full h-full object-cover">
                </div>
            </div>

            <div class="fade-in space-y-6">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                    <?php echo e(t('about_philosophy_title')); ?>
                </h2>
                <div class="space-y-4 text-lg text-gray-600 leading-relaxed">
                    <p><?php echo e(t('about_philosophy_p1')); ?></p>
                    <p><?php echo e(t('about_philosophy_p2')); ?></p>
                    <p><?php echo e(t('about_philosophy_p3')); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Team -->
<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?php echo e(t('about_team_title')); ?>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                <?php echo e(t('about_team_subtitle')); ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 fade-in">
            <div class="bg-white rounded-2xl p-8 shadow-lg text-center card-hover">
                <div class="relative w-32 h-32 mx-auto mb-6 rounded-full overflow-hidden">
                    <img src="assets/images/1.webp" alt="<?php echo e(t('about_team_master_title')); ?>" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo e(t('about_team_master_title')); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo e(t('about_team_master_role')); ?></p>
                <p class="text-sm text-gray-500"><?php echo e(t('about_team_master_experience')); ?></p>
            </div>

            <div class="bg-white rounded-2xl p-8 shadow-lg text-center card-hover">
                <div class="relative w-32 h-32 mx-auto mb-6 rounded-full overflow-hidden">
                    <img src="assets/images/2.webp" alt="<?php echo e(t('about_team_cosmetologist_title')); ?>" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo e(t('about_team_cosmetologist_title')); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo e(t('about_team_cosmetologist_role')); ?></p>
                <p class="text-sm text-gray-500"><?php echo e(t('about_team_cosmetologist_experience')); ?></p>
            </div>

            <div class="bg-white rounded-2xl p-8 shadow-lg text-center card-hover">
                <div class="relative w-32 h-32 mx-auto mb-6 rounded-full overflow-hidden">
                    <img src="assets/images/3.webp" alt="<?php echo e(t('about_team_nail_title')); ?>" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo e(t('about_team_nail_title')); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo e(t('about_team_nail_role')); ?></p>
                <p class="text-sm text-gray-500"><?php echo e(t('about_team_nail_experience')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-20 bg-olive-600 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                <?php echo e(t('about_choose_us_title')); ?>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 fade-in">
            <div class="text-center">
                <div class="bg-white/20 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-certificate text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3"><?php echo e(t('about_choose_us_professionalism')); ?></h3>
                <p class="text-olive-100"><?php echo e(t('about_choose_us_professionalism_desc')); ?></p>
            </div>

            <div class="text-center">
                <div class="bg-white/20 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-gem text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3"><?php echo e(t('about_choose_us_quality')); ?></h3>
                <p class="text-olive-100"><?php echo e(t('about_choose_us_quality_desc')); ?></p>
            </div>

            <div class="text-center">
                <div class="bg-white/20 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3"><?php echo e(t('about_choose_us_individuality')); ?></h3>
                <p class="text-olive-100"><?php echo e(t('about_choose_us_individuality_desc')); ?></p>
            </div>

            <div class="text-center">
                <div class="bg-white/20 rounded-full w-20 h-20 flex items-center justify-center.mx-auto mb-4">
                    <i class="fas fa-clock text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3"><?php echo e(t('about_choose_us_comfort')); ?></h3>
                <p class="text-olive-100"><?php echo e(t('about_choose_us_comfort_desc')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?php echo e(t('about_cta_title')); ?>
            </h2>
            <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                <?php echo e(t('about_cta_text')); ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.location.href='index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>#booking'" 
                        class="btn-primary inline-flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php echo e(t('book_now')); ?>
                </button>
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