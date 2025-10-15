<?php
require_once __DIR__ . '/config.php';

// Кэш-заголовки (без агрессии для HTML, агрессивно для статики через сервер)
if (!headers_sent()) {
    // Для HTML — короткий кэш + ETag
    header('Cache-Control: max-age=300, must-revalidate'); // 5 минут
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    // ETag на основе URL и языка
    $etag = '"' . md5(($CANONICAL ?: getCurrentUrl()) . '|' . CURRENT_LANG) . '"';
    header('ETag: ' . $etag);
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
        header('HTTP/1.1 304 Not Modified');
        exit;
    }
}

if (empty($OG_IMAGE)) {
    $OG_IMAGE = (defined('SITE_URL') ? SITE_URL : '') . '/assets/images/1.webp';
}

$meta = [
    'title' => $TITLE ?? 'Krása štúdio OK — Ružinov, Bratislava',
    'description' => $DESCRIPTION ?? '',
    'keywords' => $KEYWORDS ?? '',
];

// Корректные локали для OG
$ogLocaleMap = [
    'sk' => 'sk_SK',
    'ru' => 'ru_RU',
    'ua' => 'uk_UA',
];
$ogLocale = $ogLocaleMap[CURRENT_LANG] ?? 'sk_SK';
?>
<!DOCTYPE html>
<html lang="<?php echo CURRENT_LANG; ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo htmlspecialchars($meta['title'], ENT_QUOTES, 'UTF-8'); ?></title>
    
    <?php if (!empty($meta['description'])): ?>
        <meta name="description" content="<?php echo htmlspecialchars($meta['description'], ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    
    <?php if (!empty($meta['keywords'])): ?>
        <meta name="keywords" content="<?php echo htmlspecialchars($meta['keywords'], ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    
    <meta name="author" content="Krása štúdio OK">

    <!-- Robots -->
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    
    <?php if (!empty($CANONICAL)): ?>
        <link rel="canonical" href="<?php echo htmlspecialchars($CANONICAL, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <?php
    // Hreflang alternates (SK/RU/UA). Для UA используем ISO-код "uk".
    $languages   = ['sk','ru','ua'];
    $hreflangMap = ['sk' => 'sk', 'ru' => 'ru', 'ua' => 'uk'];
    $altLinks    = [];

    if ($currentScript === 'blog-post.php') {
        $slugKey = $_GET['slug'] ?? '';
        if ($slugKey && isset($metaData['blog-post.php'][$slugKey])) {
            foreach ($languages as $lng) {
                $href = $metaData['blog-post.php'][$slugKey][$lng]['canonical'] ?? '';
                if ($href) {
                    $altLinks[$hreflangMap[$lng]] = $href;
                }
            }
        }
    } else {
        if (isset($metaData[$currentScript])) {
            foreach ($languages as $lng) {
                $href = $metaData[$currentScript][$lng]['canonical'] ?? '';
                if ($href) {
                    $altLinks[$hreflangMap[$lng]] = $href;
                }
            }
        }
    }

    // Выводим <link rel="alternate" hreflang="...">
    foreach ($altLinks as $hl => $href) {
        echo '<link rel="alternate" hreflang="' . htmlspecialchars($hl, ENT_QUOTES, 'UTF-8') . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }

    // x-default → на SK (или на canonical, если SK нет)
    $xDefault = $altLinks['sk'] ?? ($CANONICAL ?? '');
    if ($xDefault) {
        echo '<link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($xDefault, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }
    ?>
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($OG_TITLE, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($OG_DESCRIPTION, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($OG_IMAGE ?: ((defined('SITE_URL') ? SITE_URL : '') . '/assets/images/1.webp'), ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($CANONICAL ?: getCurrentUrl(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($OG_TYPE, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:locale" content="<?php echo htmlspecialchars($ogLocale, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:site_name" content="Krása štúdio OK">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($OG_TITLE, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($OG_DESCRIPTION, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($OG_IMAGE ?: ((defined('SITE_URL') ? SITE_URL : '') . '/assets/images/1.webp'), ENT_QUOTES, 'UTF-8'); ?>">
    
    <?php
    $googleMapsUrl = 'https://maps.app.goo.gl/NRZ8C2yYqZVEJBML8';

    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'BeautySalon',
        '@id' => $CANONICAL ?: (defined('SITE_URL') ? SITE_URL : ''),
        'name' => 'Krása štúdio OK',
        'description' => $meta['description'] ?? '',
        'url' => defined('SITE_URL') ? SITE_URL : ($CANONICAL ?: getCurrentUrl()),
        'telephone' => '+' . (defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : (getenv('WHATSAPP_NUMBER') ?: '421915310337')),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Tomášikova 11',
            'postalCode' => '82103',
            'addressLocality' => 'Bratislava',
            'addressCountry' => 'SK'
        ],
        'openingHoursSpecification' => [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
            'opens' => '09:00',
            'closes' => '21:00'
        ]],
        'priceRange' => '€€',
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => '48.155708',
            'longitude' => '17.163077'
        ],
        // логотип компании (квадратный, PNG), используем твой файл
        'logo' => [
            '@type' => 'ImageObject',
            'url' => (defined('SITE_URL') ? SITE_URL : '') . '/assets/images/Mini-grey-noback.png',
            'width' => 512,
            'height' => 512
        ],
        // обложка для организации (широкая)
        'image' => (defined('SITE_URL') ? SITE_URL : '') . '/assets/images/1.webp',
        'sameAs' => [
            'https://www.facebook.com/Krasa.Studio.OK.Bratislava',
            'https://www.instagram.com/olena.krasastudio/',
            'https://maps.app.goo.gl/NRZ8C2yYqZVEJBML8',
            'https://maps.google.com/?cid=11201997670638317428'
        ],
        'hasMap' => $googleMapsUrl,
    ];

    echo '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    ?>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'olive': {
                            50: '#f7f8f0',
                            100: '#eef0dc',
                            200: '#dde2bc',
                            300: '#c5cd93',
                            400: '#afb873',
                            500: '#99a555',
                            600: '#7d8643',
                            700: '#626a35',
                            800: '#50552c',
                            900: '#444a27',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/main.css">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
    <script> window.recaptchaSiteKey = '<?php echo RECAPTCHA_SITE_KEY; ?>';</script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/Mini-grey-noback.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/Mini-grey-noback.png">

    <?php include_once 'header-extra.php'; ?>

    <script>
    window.lang = '<?php echo CURRENT_LANG; ?>';
    window.translations = {
        select_category: '<?php echo addslashes(t('select_category')); ?>',
        select_service: '<?php echo addslashes(t('select_service')); ?>',
        select_date: '<?php echo addslashes(t('select_date')); ?>',
        select_time: '<?php echo addslashes(t('select_time')); ?>',
        time_not_available: '<?php echo addslashes(t('time_not_available')); ?>',
        book_now: '<?php echo addslashes(t('book_now')); ?>',
        booking_success_title: '<?php echo addslashes(t('booking_success_title')); ?>',
        booking_success_message: '<?php echo addslashes(t('booking_success_message')); ?>',
        booking_success_button: '<?php echo addslashes(t('booking_success_button')); ?>',

        // WhatsApp keys (обязательно для корректной сборки короткого/длинного сообщений)
        whatsapp_greeting: '<?php echo addslashes(t('whatsapp_greeting')); ?>',
        whatsapp_message_with_service: '<?php echo addslashes(t('whatsapp_message_with_service')); ?>',
        whatsapp_fallback_service: '<?php echo addslashes(t('whatsapp_fallback_service')); ?>',
        whatsapp_message_default: '<?php echo addslashes(t('whatsapp_message_default')); ?>',
        whatsapp_message_short: '<?php echo addslashes(t('whatsapp_message_short')); ?>'
    };
    </script>
</head>
<body class="antialiased bg-white">
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/body-extra.php'; ?>
    <!-- Header -->
    <header id="header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="/<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="flex items-center space-x-2">
                    <div class="relative w-12 h-12">
                        <img src="assets/images/Mini-grey-noback.png" alt="Krása štúdio OK" class="w-full h-full object-contain">
                    </div>
                    <span class="font-bold text-xl text-olive-600">Krása štúdio "OK"</span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-8">
                <a href="/<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                       class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium <?php echo ($page ?? '') === 'home' ? 'text-olive-600' : ''; ?>">
                        <?php echo t('home'); ?>
                    </a>
                    <a href="about.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                       class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium <?php echo ($page ?? '') === 'about' ? 'text-olive-600' : ''; ?>">
                        <?php echo t('about'); ?>
                    </a>
                    <a href="services.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                       class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium <?php echo ($page ?? '') === 'services' ? 'text-olive-600' : ''; ?>">
                        <?php echo t('services'); ?>
                    </a>
                    <a href="pricing.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                       class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium <?php echo ($page ?? '') === 'pricing' ? 'text-olive-600' : ''; ?>">
                        <?php echo t('pricing'); ?>
                    </a>
                    <a href="gallery.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                       class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium <?php echo ($page ?? '') === 'gallery' ? 'text-olive-600' : ''; ?>">
                        <?php echo t('gallery'); ?>
                    </a>
                    <a href="blog.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                       class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium <?php echo ($page ?? '') === 'blog' ? 'text-olive-600' : ''; ?>">
                        <?php echo t('blog'); ?>
                    </a>
                    <a href="contacts.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                       class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium <?php echo ($page ?? '') === 'contacts' ? 'text-olive-600' : ''; ?>">
                        <?php echo t('contacts'); ?>
                    </a>
                </nav>

                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    <!-- Language Switcher -->
                    <div class="relative">
                        <select onchange="changeLanguage(this.value)" class="lang-select bg-transparent border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-olive-600">
                            <option value="sk" <?php echo CURRENT_LANG === 'sk' ? 'selected' : ''; ?>>SK</option>
                            <option value="ru" <?php echo CURRENT_LANG === 'ru' ? 'selected' : ''; ?>>RU</option>
                            <option value="ua" <?php echo CURRENT_LANG === 'ua' ? 'selected' : ''; ?>>UA</option>
                        </select>
                    </div>
                    
                    <button onclick="scrollToBooking()" class="hidden sm:inline-flex bg-olive-600 hover:bg-olive-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                        <?php echo t('book_now'); ?>
                    </button>

                    <!-- Mobile menu button -->
                    <button id="mobile-menu-button" class="lg:hidden p-2 rounded-md text-gray-700 hover:text-olive-600 focus:outline-none">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="lg:hidden bg-white border-t border-gray-200 py-4 hidden">
                <nav class="flex flex-col space-y-4">
                    <a href="/<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium px-4">
                        <?php echo t('home'); ?>
                    </a>
                    <a href="about.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium px-4">
                        <?php echo t('about'); ?>
                    </a>
                    <a href="services.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium px-4">
                        <?php echo t('services'); ?>
                    </a>
                    <a href="pricing.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium px-4">
                        <?php echo t('pricing'); ?>
                    </a>
                    <a href="gallery.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium px-4">
                        <?php echo t('gallery'); ?>
                    </a>
                    <a href="blog.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium px-4">
                        <?php echo t('blog'); ?>
                    </a>
                    <a href="contacts.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium px-4">
                        <?php echo t('contacts'); ?>
                    </a>
                    <div class="px-4 pt-2">
                        <button onclick="scrollToBooking()" class="w-full bg-olive-600 hover:bg-olive-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                            <?php echo t('book_now'); ?>
                        </button>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="flex-grow pt-16">