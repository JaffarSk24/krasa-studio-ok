
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
                <h2 class="text-3xl font-bold text-gray-900 mb-8">
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
                            <p class="text-gray-600">+421 905 123 456</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-olive-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-envelope text-olive-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">Email</h3>
                            <p class="text-gray-600">info@krasastudio.sk</p>
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
                            <p class="text-gray-600">Bratislava, Slovensko</p>
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
                                <p><?php echo e(t('hours_schedule')); ?></p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php echo CURRENT_LANG === 'sk' ? 'Sobota a nedeľa: Zatvorené' : 
                                              (CURRENT_LANG === 'ru' ? 'Суббота и воскресенье: Закрыто' : 
                                               'Субота та неділя: Зачинено'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Links & WhatsApp -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-900"><?php echo e(t('follow_us')); ?></h3>
                    <div class="flex gap-4">
                        <?php 
                        $whatsappNumber = '+421905123456';
                        $whatsappMessage = urlencode(t('whatsapp_message_default'));
                        ?>
                        
                        <a href="https://wa.me/<?php echo str_replace('+', '', $whatsappNumber); ?>?text=<?php echo $whatsappMessage; ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center">
                            <i class="fab fa-whatsapp mr-2 text-lg"></i>
                            WhatsApp
                        </a>
                        
                        <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center">
                            <i class="fab fa-facebook mr-2"></i>
                            Facebook
                        </a>
                        
                        <a href="#" class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 flex items-center">
                            <i class="fab fa-instagram mr-2"></i>
                            Instagram
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Google Map -->
            <div class="fade-in">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">
                    <?php echo CURRENT_LANG === 'sk' ? 'Kde nás nájdete' : (CURRENT_LANG === 'ru' ? 'Где нас найти' : 'Де нас знайти'); ?>
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
                        <?php echo CURRENT_LANG === 'sk' ? 'Ako sa k nám dostať' : (CURRENT_LANG === 'ru' ? 'Как до нас добраться' : 'Як до нас дістатися'); ?>
                    </h4>
                    <p class="text-gray-600">
                        <?php echo CURRENT_LANG === 'sk' ? 'Naše štúdio sa nachádza v centre Bratislavy s ľahkým prístupom verejnou dopravou aj autom. Parkovacie miesta sú dostupné v okolí.' : 
                                  (CURRENT_LANG === 'ru' ? 'Наша студия находится в центре Братиславы с легким доступом общественным транспортом и на автомобиле. Парковочные места доступны в округе.' : 
                                   'Наша студія розташована в центрі Братислави з легким доступом громадським транспортом та на автомобілі. Паркувальні місця доступні в околицях.'); ?>
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
                <?php echo CURRENT_LANG === 'sk' ? 'Napíšte nám' : (CURRENT_LANG === 'ru' ? 'Напишите нам' : 'Напишіть нам'); ?>
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
                                   placeholder="+421 905 123 456"
                                   class="form-control">
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
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
                                  placeholder="<?php echo CURRENT_LANG === 'sk' ? 'Napíšte nám svoju správu...' : (CURRENT_LANG === 'ru' ? 'Напишите нам ваше сообщение...' : 'Напишіть нам ваше повідомлення...'); ?>"
                                  class="form-control"></textarea>
                    </div>
                    
                    <div class="text-sm text-gray-500">
                        * <?php echo CURRENT_LANG === 'sk' ? 'Povinné polia' : (CURRENT_LANG === 'ru' ? 'Обязательные поля' : 'Обов\'язкові поля'); ?>
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
                <?php echo CURRENT_LANG === 'sk' ? 'Často kladené otázky' : (CURRENT_LANG === 'ru' ? 'Часто задаваемые вопросы' : 'Часто поставлені питання'); ?>
            </h2>
        </div>
        
        <div class="space-y-4 fade-in">
            <!-- FAQ Item 1 -->
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="font-semibold text-gray-900">
                        <?php echo CURRENT_LANG === 'sk' ? 'Ako si môžem rezervovať termín?' : 
                                  (CURRENT_LANG === 'ru' ? 'Как я могу забронировать время?' : 
                                   'Як я можу забронювати час?'); ?>
                    </h3>
                    <i class="fas fa-chevron-down accordion-icon text-olive-600"></i>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p class="text-gray-600">
                            <?php echo CURRENT_LANG === 'sk' ? 'Termín si môžete rezervovať online cez náš rezervačný formulár na hlavnej stránke, telefonicky na +421 905 123 456, alebo cez WhatsApp.' : 
                                      (CURRENT_LANG === 'ru' ? 'Время можно забронировать онлайн через нашу форму бронирования на главной странице, по телефону +421 905 123 456, или через WhatsApp.' : 
                                       'Час можна забронювати онлайн через нашу форму бронювання на головній сторінці, по телефону +421 905 123 456, або через WhatsApp.'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 2 -->
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="font-semibold text-gray-900">
                        <?php echo CURRENT_LANG === 'sk' ? 'Aké sú vaše otváracie hodiny?' : 
                                  (CURRENT_LANG === 'ru' ? 'Какие у вас часы работы?' : 
                                   'Які у вас години роботи?'); ?>
                    </h3>
                    <i class="fas fa-chevron-down accordion-icon text-olive-600"></i>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p class="text-gray-600">
                            <?php echo e(t('hours_schedule')); ?>. 
                            <?php echo CURRENT_LANG === 'sk' ? 'Sobota a nedeľa máme zatvorené.' : 
                                      (CURRENT_LANG === 'ru' ? 'Суббота и воскресенье у нас закрыто.' : 
                                       'Субота та неділя у нас зачинено.'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 3 -->
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="font-semibold text-gray-900">
                        <?php echo CURRENT_LANG === 'sk' ? 'Môžem zrušiť alebo presunúť rezerváciu?' : 
                                  (CURRENT_LANG === 'ru' ? 'Могу ли я отменить или перенести бронирование?' : 
                                   'Чи можу я скасувати або перенести бронювання?'); ?>
                    </h3>
                    <i class="fas fa-chevron-down accordion-icon text-olive-600"></i>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p class="text-gray-600">
                            <?php echo CURRENT_LANG === 'sk' ? 'Áno, rezerváciu môžete zrušiť alebo presunúť najneskôr 24 hodín pred plánovaným termínom. Kontaktujte nás telefonicky alebo cez WhatsApp.' : 
                                      (CURRENT_LANG === 'ru' ? 'Да, бронирование можно отменить или перенести не позднее чем за 24 часа до планируемого времени. Свяжитесь с нами по телефону или через WhatsApp.' : 
                                       'Так, бронювання можна скасувати або перенести не пізніше ніж за 24 години до планованого часу. Зв\'яжіться з нами по телефону або через WhatsApp.'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- FAQ Item 4 -->
            <div class="accordion-item">
                <div class="accordion-header">
                    <h3 class="font-semibold text-gray-900">
                        <?php echo CURRENT_LANG === 'sk' ? 'Aké spôsoby platby prijímate?' : 
                                  (CURRENT_LANG === 'ru' ? 'Какие способы оплаты вы принимаете?' : 
                                   'Які способи оплати ви приймаєте?'); ?>
                    </h3>
                    <i class="fas fa-chevron-down accordion-icon text-olive-600"></i>
                </div>
                <div class="accordion-content">
                    <div class="accordion-body">
                        <p class="text-gray-600">
                            <?php echo CURRENT_LANG === 'sk' ? 'Prijímame platby v hotovosti aj kartou. Všetky hlavné platobné karty sú akceptované.' : 
                                      (CURRENT_LANG === 'ru' ? 'Мы принимаем оплату наличными и картой. Принимаются все основные платежные карты.' : 
                                       'Ми приймаємо оплату готівкою та карткою. Приймаються всі основні платіжні картки.'); ?>
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
