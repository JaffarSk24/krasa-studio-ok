<?php
// includes/blog_post_cta.php
// Рендерит CTA-блок записи в конце статьи по $currentSlug из blog-post.php

if (!isset($currentSlug)) {
    // если забыли передать — не падаем
    return;
}

// Маппинг slug -> {category_id, service_id}
$ctaMap = [
    'bioenzymova-terapia' => [
        'category_id' => '169c4548-5d28-4976-b4ee-50afcecf3978',
        'service_id'  => '61d013e4-9d38-4237-a25a-f4efde5227fb',
        // опционально: кастомное имя услуги по языкам, если нужно переопределить
        'service_name' => [
            'sk' => 'Bioenzýmová terapia',
            'ru' => 'Биоэнзимная терапия',
            'ua' => 'Біоензимна терапія',
        ],
        // опционально: имя категории по языкам (для WhatsApp текста)
        'category_name' => [
            'sk' => '',
            'ru' => '',
            'ua' => '',
        ],
    ],
    'laserova-epilacia' => [
        'category_id' => 'e665689f-df47-46b8-9d33-b15096e61c67',
        'service_id'  => 'f0e0c0b1-eb6f-4b65-b658-01e7c04cb7f4',
        // опционально: кастомное имя услуги по языкам, если нужно переопределить
        'service_name' => [
            'sk' => 'Laserová epilácia',
            'ru' => 'Лазерная эпиляция',
            'ua' => 'Лазерна епіляція',
        ],
        // опционально: имя категории по языкам (для WhatsApp текста)
        'category_name' => [
            'sk' => '',
            'ru' => '',
            'ua' => '',
        ],
    ],
    'masaze-tvare' => [
        'category_id' => '66eca6d8-1655-48bf-8463-1178b40bac2a',
        'service_id'  => 'd9f51c6d-f220-4d5f-8055-a4eb471b293d',
        // опционально: кастомное имя услуги по языкам, если нужно переопределить
        'service_name' => [
            'sk' => 'Masáže tváre, krku a dekoltu',
            'ru' => 'Массаж лица, шеи и декольте',
            'ua' => 'Масаж обличчя, шиї та декольте',
        ],
        // опционально: имя категории по языкам (для WhatsApp текста)
        'category_name' => [
            'sk' => '',
            'ru' => '',
            'ua' => '',
        ],
    ],
];

// Если slug не в маппинге — не рендерим блок
if (!isset($ctaMap[$currentSlug])) {
    return;
}

$cfg = $ctaMap[$currentSlug];

$ctaCategoryId = $cfg['category_id'];
$ctaServiceId  = $cfg['service_id'];

$lang = defined('CURRENT_LANG') ? CURRENT_LANG : 'sk';

// Имя услуги: из маппинга или подтянем из БД (services)
$serviceName = '';
if (!empty($cfg['service_name'][$lang])) {
    $serviceName = $cfg['service_name'][$lang];
} else {
    try {
        if (!isset($conn)) {
            $db = new Database();
            $conn = $db->getConnection();
        }
        $st = $conn->prepare("SELECT name_sk, name_ru, name_ua FROM services WHERE id = :sid LIMIT 1");
        $st->execute([':sid' => $ctaServiceId]);
        if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $serviceName = $row["name_{$lang}"] ?? ($row['name_sk'] ?? '');
        }
    } catch (Exception $e) {
        // ignore
    }
}
if ($serviceName === '') {
    $serviceName = $lang === 'ru' ? 'Биоэнзимная терапия' : ($lang === 'ua' ? 'Біоензимна терапія' : 'Bioenzýmová terapia');
}

// Имя категории для WhatsApp текста (если задано в маппинге)
$prefillCategoryName = $cfg['category_name'][$lang] ?? '';

// WhatsApp номер и текст
$waDigits = function_exists('getWhatsappNumber') ? getWhatsappNumber(true) : preg_replace('/\D+/', '', (defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : ''));
if (function_exists('buildWhatsappMessage')) {
    $waText = buildWhatsappMessage($prefillCategoryName, $serviceName, $lang);
} else {
    $greetings = [
        'ru' => 'Здравствуйте! Хочу записаться на процедуру: ',
        'ua' => 'Добрий день! Хочу записатися на процедуру: ',
        'sk' => 'Dobrý deň! Chcem sa objednať na procedúru: ',
    ];
    $waText = urlencode(($greetings[$lang] ?? $greetings['sk']) . $serviceName);
}
$whatsappLink = 'https://wa.me/' . $waDigits . '?text=' . $waText;

// Текст заголовка/подзаголовка
$title = $lang === 'ru'
    ? 'Желаете записаться на процедуру «' . htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') . '» в студию красоты «OK»?'
    : ($lang === 'ua'
        ? 'Бажаєте записатися на процедуру «' . htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') . '» у студію краси «OK»?'
        : 'Chcete sa objednať na procedúru „' . htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') . '“ do štúdia krásy „OK“?');

$subtitle = $lang === 'ru'
    ? 'Выберите ближайшее доступное время.'
    : ($lang === 'ua'
        ? 'Виберіть найближчий доступний час.'
        : 'Vyberte si najbližší voľný termín.');

// Цель перехода для prefill
$target = (defined('CURRENT_LANG') && defined('DEFAULT_LANGUAGE') && CURRENT_LANG !== DEFAULT_LANGUAGE)
    ? 'index.php?lang=' . CURRENT_LANG . '#booking'
    : 'index.php#booking';
?>

<section class="my-12">
  <div class="bg-[#7B874B] text-white rounded-xl px-6 py-10 text-center">
    <h3 class="text-2xl md:text-3xl font-bold mb-4"><?php echo $title; ?></h3>
    <p class="text-base md:text-lg opacity-90 mb-6"><?php echo $subtitle; ?></p>

    <div class="flex items-center justify-center gap-3 md:gap-4 flex-wrap">
      <button
        type="button"
        class="btn-primary px-4 py-2 flex items-center prefill-booking-btn bg-white text-[#7B874B] hover:bg-gray-100 rounded-lg"
        data-category-id="<?php echo htmlspecialchars($ctaCategoryId, ENT_QUOTES, 'UTF-8'); ?>"
        data-service-id="<?php echo htmlspecialchars($ctaServiceId, ENT_QUOTES, 'UTF-8'); ?>"
        data-category-name="<?php echo htmlspecialchars($prefillCategoryName, ENT_QUOTES, 'UTF-8'); ?>"
        data-service-name="<?php echo htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8'); ?>"
        data-lang="<?php echo htmlspecialchars(CURRENT_LANG, ENT_QUOTES, 'UTF-8'); ?>"
        data-default-lang="<?php echo htmlspecialchars(DEFAULT_LANGUAGE, ENT_QUOTES, 'UTF-8'); ?>"
        data-target="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>">
        <i class="fas fa-calendar-alt mr-2"></i>
        <span><?php echo e(t('book_service')); ?></span>
      </button>

      <a href="<?php echo e($whatsappLink); ?>"
         target="_blank"
         rel="noopener noreferrer"
         class="border border-white/80 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center hover:bg-white hover:text-[#7B874B]">
        <i class="fab fa-whatsapp mr-2"></i>
        <span>WhatsApp</span>
      </a>
    </div>
  </div>
</section>