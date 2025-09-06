
<!DOCTYPE html>
<html lang="<?php echo CURRENT_LANG; ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php 
    $meta = getPageMeta($page ?? 'home', $pageData ?? []);
    ?>
    
    <title><?php echo e($meta['title']); ?></title>
    <meta name="description" content="<?php echo e($meta['description']); ?>">
    <meta name="keywords" content="<?php echo e($meta['keywords']); ?>">
    <meta name="author" content="Krása štúdio OK">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo e($meta['title']); ?>">
    <meta property="og:description" content="<?php echo e($meta['description']); ?>">
    <meta property="og:image" content="<?php echo $meta['og_image']; ?>">
    <meta property="og:url" content="<?php echo getCurrentUrl(); ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?php echo CURRENT_LANG; ?>_<?php echo strtoupper(CURRENT_LANG); ?>">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BeautySalon",
        "name": "Krása štúdio OK",
        "description": "<?php echo e($meta['description']); ?>",
        "url": "<?php echo SITE_URL; ?>",
        "telephone": "+421905123456",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Bratislava",
            "addressCountry": "Slovakia"
        },
        "openingHours": "Mo-Fr 09:00-21:00",
        "priceRange": "€€"
    }
    </script>
    
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
                            600: '#7d8643', // Primary olive color from design
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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/main.css">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/Mini Логотип без фона.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/Mini Логотип без фона.png">
    
    <?php include_once 'header-extra.php'; ?>
</head>
<body class="antialiased bg-white">
    <!-- Header -->
    <header id="header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="flex items-center space-x-2">
                    <div class="relative w-12 h-12">
                        <img src="assets/images/Mini Логотип без фона.png" alt="Krása štúdio OK" class="w-full h-full object-contain">
                    </div>
                    <span class="font-bold text-xl text-olive-600">Krása štúdio "OK"</span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-8">
                    <a href="index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
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
                        <select onchange="changeLanguage(this.value)" class="bg-transparent border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-olive-600">
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
                    <a href="index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="text-gray-700 hover:text-olive-600 transition-colors duration-200 font-medium px-4">
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
