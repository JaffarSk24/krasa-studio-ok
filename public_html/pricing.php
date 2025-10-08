
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
            <div class="space-y-16 fade-in">
                <?php foreach ($servicesByCategory as $categoryData): ?>
                    <?php 
                    $category = $categoryData['category'];
                    $categoryServices = $categoryData['services'];
                    $categoryName = getLocalizedField($category, 'category_name');
                    ?>
                    
                    <div>
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
                                    <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-lg card-hover">
                                        <div class="text-center">
                                            <!-- Service Icon -->
                                            <div class="w-16 h-16 bg-olive-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                                <i class="fas fa-spa text-olive-600 text-2xl"></i>
                                            </div>
                                            
                                            <!-- Service Name -->
                                            <h3 class="text-xl font-bold text-gray-900 mb-3">
                                                <?php echo e(getLocalizedField($service, 'name')); ?>
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
                                            <button onclick="window.location.href='index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>#booking'" 
                                                    class="w-full btn-primary mb-3">
                                                <?php echo e(t('book_service')); ?>
                                            </button>
                                            
                                            <!-- WhatsApp Button -->
                                            <?php 
                                            $whatsappNumber = $service['whatsapp_number'] ?: '+421905123456';
                                            $serviceName = getLocalizedField($service, 'name');
                                            $whatsappMessage = urlencode(
                                                (CURRENT_LANG === 'sk' ? "Zdravím! Chcel by som sa opýtať na službu: " : 
                                                (CURRENT_LANG === 'ru' ? "Здравствуйте! Хотел бы узнать об услуге: " : 
                                                "Здравствуйте! Хотів би дізнатися про послугу: ")) . $serviceName
                                            );
                                            ?>
                                            
                                            <a href="https://wa.me/<?php echo str_replace('+', '', $whatsappNumber); ?>?text=<?php echo $whatsappMessage; ?>" 
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
                            <?php if (CURRENT_LANG === 'sk'): ?>
                                <?php if (stripos($categoryName, 'kadernícke') !== false || stripos($categoryName, 'vlasy') !== false): ?>
                                    <p>Naše kadernícke služby zahŕňajú kompletné služby pre starostlivosť o vlasy. Ponúkame strihanie, farbenie, melírovanie, trvalú ondulace a styling pre dámy aj pánov. Používame len kvalitné produkty svetových značiek, ktoré šetria vaše vlasy a zabezpečujú dlhotrvajúce výsledky. Naši kaderníci sú pravidelně školení v najnovších technikách a trendoch.</p>
                                <?php elseif (stripos($categoryName, 'kozmetika') !== false || stripos($categoryName, 'ošetrenie') !== false): ?>
                                    <p>Kozmetické ošetrenia v našom štúdiu sú zamerané na komplexnú starostlivosť o pleť. Ponúkame čistenie pleti, anti-age ošetrenia, hydratačné masky a špecializované procedúry pre rôzne typy pleti. Všetky naše kozmetické služby sú vykonávané certifikovanými kozmetičkami s využitím moderného vybavenia.</p>
                                <?php elseif (stripos($categoryName, 'manikúra') !== false || stripos($categoryName, 'nechty') !== false): ?>
                                    <p>Naše služby manikúry a pedikúry zaručujú profesionálnu starostlivosť o vaše nechty a ruky. Ponúkame klasickú manikúru, gél lak, predlžovanie nechtov, nail art a ošetrovanie. Všetky nástroje sú sterilizované a používame len kvalitné materiály, ktoré sú bezpečné pre vaše zdravie.</p>
                                <?php endif; ?>
                            <?php elseif (CURRENT_LANG === 'ru'): ?>
                                <?php if (stripos($categoryName, 'парикмахер') !== false || stripos($categoryName, 'волос') !== false): ?>
                                    <p>Наши парикмахерские услуги включают полный спектр услуг по уходу за волосами. Мы предлагаем стрижку, окрашивание, мелирование, химическую завивку и укладку для женщин и мужчин. Используем только качественные продукты мировых брендов, которые бережно воздействуют на ваши волосы и обеспечивают долговременные результаты.</p>
                                <?php elseif (stripos($categoryName, 'косметолог') !== false || stripos($categoryName, 'процедур') !== false): ?>
                                    <p>Косметические процедуры в нашей студии направлены на комплексный уход за кожей. Мы предлагаем чистку лица, антивозрастные процедуры, увлажняющие маски и специализированные процедуры для различных типов кожи. Все наши косметические услуги выполняются сертифицированными косметологами.</p>
                                <?php elseif (stripos($categoryName, 'маникюр') !== false || stripos($categoryName, 'ногт') !== false): ?>
                                    <p>Наши услуги маникюра и педикюра гарантируют профессиональный уход за вашими ногтями и руками. Предлагаем классический маникюр, гель-лак, наращивание ногтей, нейл-арт и лечебные процедуры. Все инструменты стерилизуются, используем только качественные материалы.</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (stripos($categoryName, 'перукар') !== false || stripos($categoryName, 'волосс') !== false): ?>
                                    <p>Наші перукарські послуги включають повний спектр послуг з догляду за волоссям. Пропонуємо стрижку, фарбування, мелювання, хімічну завивку та укладання для жінок та чоловіків. Використовуємо лише якісні продукти світових брендів, які бережно впливають на ваше волосся.</p>
                                <?php elseif (stripos($categoryName, 'косметолог') !== false || stripos($categoryName, 'процедур') !== false): ?>
                                    <p>Косметичні процедури в нашій студії спрямовані на комплексний догляд за шкірою. Пропонуємо чищення обличчя, антивікові процедури, зволожуючі маски та спеціалізовані процедури для різних типів шкіри. Всі косметичні послуги виконуються сертифікованими косметологами.</p>
                                <?php elseif (stripos($categoryName, 'манікюр') !== false || stripos($categoryName, 'нігт') !== false): ?>
                                    <p>Наші послуги манікюру та педикюру гарантують професійний догляд за вашими нігтями та руками. Пропонуємо класичний манікюр, гель-лак, нарощування нігтів, нейл-арт та лікувальні процедури. Всі інструменти стерилізуються.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <i class="fas fa-euro-sign text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-500 mb-4">
                    <?php echo CURRENT_LANG === 'sk' ? 'Cenník sa načítava...' : (CURRENT_LANG === 'ru' ? 'Прайс-лист загружается...' : 'Прайс-лист завантажується...'); ?>
                </h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Additional SEO Content -->
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg max-w-none fade-in">
            <?php if (CURRENT_LANG === 'sk'): ?>
                <h2>Prečo si vybrať Krása štúdio "OK" v Bratislave?</h2>
                <p>Naše štúdio krásy sa nachádza v centre Bratislavy a poskytuje širokú škálu služieb v oblasti kaderníctva, kozmetiky, manikúry a pedikúry. Našou filozofiou je poskytovanie vysoko kvalitných služieb za férové ceny.</p>
                
                <h3>Transparentné ceny bez skrytých poplatkov</h3>
                <p>Všetky ceny uvedené v našom cenníku sú finálne. Neúčtujeme žiadne dodatočné poplatky a vždy vás budeme vopred informovať o celkovej sume za poskytnuté služby. Naša cenová politika je založená na spravodlivosti a transparentnosti.</p>
                
                <h3>Kvalitné produkty a materiály</h3>
                <p>Používame výlučne overené produkty od renomovaných svetových značiek. Či už ide o farby na vlasy, kozmetické prípravky alebo materiály na manikúru, všetko je vyberané s dôrazom na kvalitu a bezpečnosť našich klientov.</p>
                
                <h3>Rezervácia a platba</h3>
                <p>Rezerváciu termínu môžete vykonať online cez náš rezervačný systém, telefonicky alebo prostredníctvom WhatsApp. Platbu prijímame v hotovosti aj kartou. Pre pravidelných klientov máme pripravené výhodné balíčky služieb.</p>
            <?php elseif (CURRENT_LANG === 'ru'): ?>
                <h2>Почему выбрать салон красоты "OK" в Братиславе?</h2>
                <p>Наша студия красоты расположена в центре Братиславы и предоставляет широкий спектр услуг в области парикмахерского дела, косметологии, маникюра и педикюра. Наша философия - предоставление высококачественных услуг по справедливым ценам.</p>
                
                <h3>Прозрачные цены без скрытых платежей</h3>
                <p>Все цены, указанные в нашем прайс-листе, являются окончательными. Мы не взимаем никаких дополнительных платежей и всегда заранее информируем о полной стоимости предоставляемых услуг. Наша ценовая политика основана на справедливости и прозрачности.</p>
                
                <h3>Качественные продукты и материалы</h3>
                <p>Мы используем исключительно проверенные продукты от известных мировых брендов. Будь то краски для волос, косметические препараты или материалы для маникюра - все выбирается с акцентом на качество и безопасность наших клиентов.</p>
                
                <h3>Бронирование и оплата</h3>
                <p>Бронирование можно осуществить онлайн через нашу систему бронирования, по телефону или через WhatsApp. Оплату принимаем наличными и картой. Для постоянных клиентов у нас есть выгодные пакеты услуг.</p>
            <?php else: ?>
                <h2>Чому обрати салон краси "OK" у Братиславі?</h2>
                <p>Наша студія краси розташована в центрі Братислави та надає широкий спектр послуг у сфері перукарської справи, косметології, манікюру та педикюру. Наша філософія - надання високоякісних послуг за справедливими цінами.</p>
                
                <h3>Прозорі ціни без прихованих платежів</h3>
                <p>Всі ціни, вказані в нашому прайс-листі, є остаточними. Ми не стягуємо жодних додаткових платежів і завжди заздалегідь інформуємо про повну вартість послуг. Наша цінова політика базується на справедливості та прозорості.</p>
                
                <h3>Якісні продукти та матеріали</h3>
                <p>Ми використовуємо виключно перевірені продукти від відомих світових брендів. Чи то фарби для волосся, косметичні препарати або матеріали для манікюру - все обирається з акцентом на якість та безпеку наших клієнтів.</p>
                
                <h3>Бронювання та оплата</h3>
                <p>Бронювання можна здійснити онлайн через нашу систему бронювання, по телефону або через WhatsApp. Оплату приймаємо готівкою та карткою. Для постійних клієнтів маємо вигідні пакети послуг.</p>
            <?php endif; ?>
        </div>
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
                <?php echo CURRENT_LANG === 'sk' ? 'Využite naše výhodné ceny a profesionálne služby.' : 
                          (CURRENT_LANG === 'ru' ? 'Воспользуйтесь нашими выгодными ценами и профессиональными услугами.' : 
                           'Скористайтеся нашими вигідними цінами та професійними послугами.'); ?>
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
