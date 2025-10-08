
<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$page = 'blog';

// Load blog posts
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT * FROM blog_posts 
    WHERE is_published = 1 
    ORDER BY published_at DESC, created_at DESC
    LIMIT 20
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no posts in database, create some sample posts
if (empty($posts)) {
    $samplePosts = [
        [
            'id' => 1,
            'title_sk' => 'Trendy v kadernícke služby na jar 2024',
            'title_ru' => 'Тренды парикмахерских услуг весной 2024',
            'title_ua' => 'Тренди перукарських послуг навесні 2024',
            'excerpt_sk' => 'Objavte najnovšie trendy vo svete kaderníctva a krás',
            'excerpt_ru' => 'Откройте для себя последние тенденции в мире парикмахерского дела',
            'excerpt_ua' => 'Відкрийте для себе останні тенденції у світі перукарської справи',
            'slug' => 'trendy-kadernicke-sluzby-jar-2024',
            'featured_image' => '1.webp',
            'published_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'id' => 2,
            'title_sk' => 'Ako správne ošetrovať vlasy doma',
            'title_ru' => 'Как правильно ухаживать за волосами дома',
            'title_ua' => 'Як правильно доглядати за волоссям вдома',
            'excerpt_sk' => 'Praktické rady pre domácu starostlivosť o vlasy',
            'excerpt_ru' => 'Практические советы по домашнему уходу за волосами',
            'excerpt_ua' => 'Практичні поради з домашнього догляду за волоссям',
            'slug' => 'ako-spravne-osetrovat-vlasy-doma',
            'featured_image' => '4.webp',
            'published_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
        ],
        [
            'id' => 3,
            'title_sk' => 'Kozmetické ošetrenia pre citlivú pleť',
            'title_ru' => 'Косметические процедуры для чувствительной кожи',
            'title_ua' => 'Косметичні процедури для чутливої шкіри',
            'excerpt_sk' => 'Špeciálne procedúry pre citlivé typy pleti',
            'excerpt_ru' => 'Специальные процедуры для чувствительных типов кожи',
            'excerpt_ua' => 'Спеціальні процедури для чутливих типів шкіри',
            'slug' => 'kozmeticke-osetrenia-pre-citlivu-plet',
            'featured_image' => '2.webp',
            'published_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
        ]
    ];
    $posts = $samplePosts;
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="pt-32 pb-20 bg-gradient-to-br from-olive-50 to-green-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                <?php echo e(t('blog_title')); ?>
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                <?php echo e(t('blog_subtitle')); ?>
            </p>
        </div>
    </div>
</section>

<!-- Blog Posts Grid -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($posts)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 fade-in">
                <?php foreach ($posts as $post): ?>
                    <article class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                        <!-- Featured Image -->
                        <div class="relative aspect-video bg-gray-200">
                            <?php if (!empty($post['featured_image'])): ?>
                                <img src="assets/images/<?php echo e($post['featured_image']); ?>" 
                                     alt="<?php echo e(getLocalizedField($post, 'title')); ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-olive-100">
                                    <i class="fas fa-newspaper text-olive-600 text-4xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Date Overlay -->
                            <div class="absolute top-4 left-4 bg-white/90 rounded-lg px-3 py-1 text-sm font-medium text-gray-900">
                                <?php 
                                $publishedAt = $post['published_at'] ?? $post['created_at'];
                                echo date('d.m.Y', strtotime($publishedAt)); 
                                ?>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                                <?php echo e(getLocalizedField($post, 'title')); ?>
                            </h2>
                            
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                <?php echo e(getLocalizedField($post, 'excerpt')); ?>
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">
                                    <?php echo e(t('published_on')); ?> <?php echo date('d.m.Y', strtotime($publishedAt)); ?>
                                </span>
                                
                                <a href="blog-post.php?slug=<?php echo urlencode($post['slug']); ?><?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '&lang=' . CURRENT_LANG : ''; ?>" 
                                   class="text-olive-600 hover:text-olive-700 font-medium inline-flex items-center">
                                    <?php echo e(t('read_more')); ?>
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 fade-in">
                <i class="fas fa-newspaper text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-500 mb-4">
                    <?php echo CURRENT_LANG === 'sk' ? 'Články sa načítavajú...' : (CURRENT_LANG === 'ru' ? 'Статьи загружаются...' : 'Статті завантажуються...'); ?>
                </h3>
                <p class="text-gray-400">
                    <?php echo CURRENT_LANG === 'sk' ? 'Čoskoro pridáme zaujímavé články o kráse a starostlivosti.' : 
                              (CURRENT_LANG === 'ru' ? 'Скоро добавим интересные статьи о красоте и уходе.' : 
                               'Незабаром додамо цікаві статті про красу та догляд.'); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?php echo CURRENT_LANG === 'sk' ? 'Odber noviniek' : (CURRENT_LANG === 'ru' ? 'Подписка на новости' : 'Підписка на новини'); ?>
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                <?php echo CURRENT_LANG === 'sk' ? 'Prihláste sa na odber a získajte najnovšie tipy o kráse a starostlivosti priamo do emailu.' : 
                          (CURRENT_LANG === 'ru' ? 'Подпишитесь и получайте последние советы о красоте и уходе прямо на email.' : 
                           'Підпишіться та отримуйте останні поради про красу та догляд прямо на email.'); ?>
            </p>
        </div>
        
        <div class="max-w-md mx-auto fade-in">
            <form class="flex gap-4">
                <input type="email" 
                       placeholder="<?php echo e(t('email')); ?>..." 
                       required 
                       class="flex-1 form-control">
                <button type="submit" class="btn-primary px-6 py-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Prihlásiť' : (CURRENT_LANG === 'ru' ? 'Подписаться' : 'Підписатися'); ?>
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="py-20 bg-olive-600 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                <?php echo CURRENT_LANG === 'sk' ? 'Naše témy' : (CURRENT_LANG === 'ru' ? 'Наши темы' : 'Наші теми'); ?>
            </h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 fade-in">
            <!-- Category 1 -->
            <div class="text-center p-6">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cut text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Kaderníctvo' : (CURRENT_LANG === 'ru' ? 'Парикмахерские услуги' : 'Перукарські послуги'); ?>
                </h3>
                <p class="text-olive-100">
                    <?php echo CURRENT_LANG === 'sk' ? 'Trendy, strihy, farbenie a starostlivosť o vlasy' : 
                              (CURRENT_LANG === 'ru' ? 'Тренды, стрижки, окрашивание и уход за волосами' : 
                               'Тренди, стрижки, фарбування та догляд за волоссям'); ?>
                </p>
            </div>

            <!-- Category 2 -->
            <div class="text-center p-6">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-spa text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Kozmetika' : (CURRENT_LANG === 'ru' ? 'Косметология' : 'Косметологія'); ?>
                </h3>
                <p class="text-olive-100">
                    <?php echo CURRENT_LANG === 'sk' ? 'Ošetrenia pleti, anti-aging a domáca starostlivosť' : 
                              (CURRENT_LANG === 'ru' ? 'Процедуры для кожи, anti-aging и домашний уход' : 
                               'Процедури для шкіри, anti-aging та домашній догляд'); ?>
                </p>
            </div>

            <!-- Category 3 -->
            <div class="text-center p-6">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-hand-sparkles text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Manikúra' : (CURRENT_LANG === 'ru' ? 'Маникюр' : 'Манікюр'); ?>
                </h3>
                <p class="text-olive-100">
                    <?php echo CURRENT_LANG === 'sk' ? 'Nail art, gél laky a starostlivosť o nechty' : 
                              (CURRENT_LANG === 'ru' ? 'Нейл-арт, гель-лаки и уход за ногтями' : 
                               'Нейл-арт, гель-лаки та догляд за нігтями'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?php echo CURRENT_LANG === 'sk' ? 'Máte otázky o kráse?' : 
                          (CURRENT_LANG === 'ru' ? 'Есть вопросы о красоте?' : 
                           'Маєте питання про красу?'); ?>
            </h2>
            <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                <?php echo CURRENT_LANG === 'sk' ? 'Náš tím expertov vám rád poradí a naplánuje individuálnu starostlivosť.' : 
                          (CURRENT_LANG === 'ru' ? 'Наша команда экспертов с радостью даст совет и спланирует индивидуальный уход.' : 
                           'Наша команда експертів із радістю порадить та сплануює індивідуальний догляд.'); ?>
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.location.href='index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>#booking'" 
                        class="btn-primary inline-flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php echo e(t('book_now')); ?>
                </button>
                
                <a href="contacts.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                   class="btn-secondary inline-flex items-center">
                    <i class="fas fa-comments mr-2"></i>
                    <?php echo e(t('contact_us')); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include 'includes/footer.php'; ?>
