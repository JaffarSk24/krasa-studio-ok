
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
            <!-- Left side - Image -->
            <div class="fade-in">
                <div class="relative aspect-square rounded-2xl overflow-hidden shadow-2xl">
                    <img src="assets/images/5.webp" 
                         alt="<?php echo e(t('about_title')); ?>" 
                         class="w-full h-full object-cover">
                </div>
            </div>

            <!-- Right side - Content -->
            <div class="fade-in space-y-6">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                    <?php echo CURRENT_LANG === 'sk' ? 'Naša filozofia' : (CURRENT_LANG === 'ru' ? 'Наша философия' : 'Наша філософія'); ?>
                </h2>
                
                <div class="space-y-4 text-lg text-gray-600 leading-relaxed">
                    <?php if (CURRENT_LANG === 'sk'): ?>
                    <p>
                        Krása štúdio "OK" je vašim spoľahlivým partnerom v oblasti krásy a starostlivosti o seba. 
                        Už viac ako 10 rokov poskytujeme profesionálne služby v centre Bratislavy.
                    </p>
                    <p>
                        Naším cieľom je, aby sa každý náš klient cítil výnimočne a spokojne. Využívame len 
                        najkvalitnejšie produkty a najmodernejšie technológie v oblasti kozmetiky a kaderníctva.
                    </p>
                    <p>
                        Náš tím skúsených profesionálov sa neustále vzdeláva a sleduje najnovšie trendy, 
                        aby vám mohol ponúknuť tie najlepšie služby.
                    </p>
                    <?php elseif (CURRENT_LANG === 'ru'): ?>
                    <p>
                        Салон красоты "OK" - ваш надежный партнер в области красоты и ухода за собой. 
                        Уже более 10 лет мы предоставляем профессиональные услуги в центре Братиславы.
                    </p>
                    <p>
                        Наша цель - чтобы каждый клиент чувствовал себя исключительным и довольным. Мы используем 
                        только самые качественные продукты и современные технологии в области косметологии и парикмахерского дела.
                    </p>
                    <p>
                        Наша команда опытных профессионалов постоянно обучается и следит за новейшими трендами, 
                        чтобы предложить вам самые лучшие услуги.
                    </p>
                    <?php else: ?>
                    <p>
                        Салон краси "OK" - ваш надійний партнер у сфері краси та догляду за собою. 
                        Вже понад 10 років ми надаємо професійні послуги у центрі Братислави.
                    </p>
                    <p>
                        Наша мета - щоб кожен клієнт відчував себе винятковим та задоволеним. Ми використовуємо 
                        лише найякісніші продукти та найсучасніші технології у сфері косметології та перукарської справи.
                    </p>
                    <p>
                        Наша команда досвідчених професіоналів постійно навчається та слідкує за найновішими трендами, 
                        щоб запропонувати вам найкращі послуги.
                    </p>
                    <?php endif; ?>
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
                <?php echo CURRENT_LANG === 'sk' ? 'Náš tím' : (CURRENT_LANG === 'ru' ? 'Наша команда' : 'Наша команда'); ?>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                <?php echo CURRENT_LANG === 'sk' ? 'Profesionáli s vášňou pre krásu' : (CURRENT_LANG === 'ru' ? 'Профессионалы со страстью к красоте' : 'Професіонали з пристрастю до краси'); ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 fade-in">
            <!-- Team Member 1 -->
            <div class="bg-white rounded-2xl p-8 shadow-lg text-center card-hover">
                <div class="relative w-32 h-32 mx-auto mb-6 rounded-full overflow-hidden">
                    <img src="assets/images/1.webp" alt="Majsterka" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    <?php echo CURRENT_LANG === 'sk' ? 'Hlavná majsterka' : (CURRENT_LANG === 'ru' ? 'Главный мастер' : 'Головна майстриня'); ?>
                </h3>
                <p class="text-gray-600 mb-4">
                    <?php echo CURRENT_LANG === 'sk' ? 'Kaderníctvo, styling' : (CURRENT_LANG === 'ru' ? 'Парикмахерские услуги, стайлинг' : 'Перукарські послуги, стайлинг'); ?>
                </p>
                <p class="text-sm text-gray-500">
                    <?php echo CURRENT_LANG === 'sk' ? '15+ rokov skúseností' : (CURRENT_LANG === 'ru' ? '15+ лет опыта' : '15+ років досвіду'); ?>
                </p>
            </div>

            <!-- Team Member 2 -->
            <div class="bg-white rounded-2xl p-8 shadow-lg text-center card-hover">
                <div class="relative w-32 h-32 mx-auto mb-6 rounded-full overflow-hidden">
                    <img src="assets/images/2.webp" alt="Kozmetička" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    <?php echo CURRENT_LANG === 'sk' ? 'Kozmetička' : (CURRENT_LANG === 'ru' ? 'Косметолог' : 'Косметологиня'); ?>
                </h3>
                <p class="text-gray-600 mb-4">
                    <?php echo CURRENT_LANG === 'sk' ? 'Kozmetické ošetrenia' : (CURRENT_LANG === 'ru' ? 'Косметические процедуры' : 'Косметичні процедури'); ?>
                </p>
                <p class="text-sm text-gray-500">
                    <?php echo CURRENT_LANG === 'sk' ? '10+ rokov skúseností' : (CURRENT_LANG === 'ru' ? '10+ лет опыта' : '10+ років досвіду'); ?>
                </p>
            </div>

            <!-- Team Member 3 -->
            <div class="bg-white rounded-2xl p-8 shadow-lg text-center card-hover">
                <div class="relative w-32 h-32 mx-auto mb-6 rounded-full overflow-hidden">
                    <img src="assets/images/3.webp" alt="Nechtová štylista" class="w-full h-full object-cover">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    <?php echo CURRENT_LANG === 'sk' ? 'Nechtová štylista' : (CURRENT_LANG === 'ru' ? 'Мастер ногтевого дизайна' : 'Стиліст манікюру'); ?>
                </h3>
                <p class="text-gray-600 mb-4">
                    <?php echo CURRENT_LANG === 'sk' ? 'Manikúra, pedikúra' : (CURRENT_LANG === 'ru' ? 'Маникюр, педикюр' : 'Манікюр, педикюр'); ?>
                </p>
                <p class="text-sm text-gray-500">
                    <?php echo CURRENT_LANG === 'sk' ? '8+ rokov skúseností' : (CURRENT_LANG === 'ru' ? '8+ лет опыта' : '8+ років досвіду'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-20 bg-olive-600 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                <?php echo CURRENT_LANG === 'sk' ? 'Prečo si vybrať nás?' : (CURRENT_LANG === 'ru' ? 'Почему выбрать нас?' : 'Чому обрати нас?'); ?>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 fade-in">
            <!-- Feature 1 -->
            <div class="text-center">
                <div class="bg-white/20 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-certificate text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Profesionalita' : (CURRENT_LANG === 'ru' ? 'Профессионализм' : 'Професіоналізм'); ?>
                </h3>
                <p class="text-olive-100">
                    <?php echo CURRENT_LANG === 'sk' ? 'Certifikovaní majstri s dlhoročnými skúsenosťami' : (CURRENT_LANG === 'ru' ? 'Сертифицированные мастера с многолетним опытом' : 'Сертифіковані майстри з багаторічним досвідом'); ?>
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="text-center">
                <div class="bg-white/20 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-gem text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Kvalita' : (CURRENT_LANG === 'ru' ? 'Качество' : 'Якість'); ?>
                </h3>
                <p class="text-olive-100">
                    <?php echo CURRENT_LANG === 'sk' ? 'Len najkvalitnejšie produkty svetových značiek' : (CURRENT_LANG === 'ru' ? 'Только качественные продукты мировых брендов' : 'Лише найякісніші продукти світових брендів'); ?>
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="text-center">
                <div class="bg-white/20 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Individualita' : (CURRENT_LANG === 'ru' ? 'Индивидуальность' : 'Індивідуальність'); ?>
                </h3>
                <p class="text-olive-100">
                    <?php echo CURRENT_LANG === 'sk' ? 'Personalizovaný prístup ku každému klientovi' : (CURRENT_LANG === 'ru' ? 'Персонализированный подход к каждому клиенту' : 'Персоналізований підхід до кожного клієнта'); ?>
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="text-center">
                <div class="bg-white/20 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Pohodlie' : (CURRENT_LANG === 'ru' ? 'Комфорт' : 'Комфорт'); ?>
                </h3>
                <p class="text-olive-100">
                    <?php echo CURRENT_LANG === 'sk' ? 'Flexibilné hodiny a príjemná atmosféra' : (CURRENT_LANG === 'ru' ? 'Гибкие часы работы и приятная атмосфера' : 'Гнучкі години роботи та приємна атмосфера'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?php echo CURRENT_LANG === 'sk' ? 'Pripravení na zmenu?' : (CURRENT_LANG === 'ru' ? 'Готовы к изменениям?' : 'Готові до змін?'); ?>
            </h2>
            <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                <?php echo CURRENT_LANG === 'sk' ? 'Rezervujte si svoj termín už dnes a prenechajte sa do rúk našich profesionálov.' : 
                          (CURRENT_LANG === 'ru' ? 'Забронируйте свое время уже сегодня и доверьтесь нашим профессионалам.' : 
                           'Забронюйте свій час вже сьогодні та довіртеся нашим професіоналам.'); ?>
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
