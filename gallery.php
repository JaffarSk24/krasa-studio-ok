
<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$page = 'gallery';

// Load gallery images
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT * FROM gallery_images 
    WHERE is_active = 1 
    ORDER BY order_num, created_at DESC
");
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no images in database, load from uploaded files
if (empty($images)) {
    $imageFiles = ['1.webp', '2.webp', '3.webp', '4.webp', '5.webp', '6.webp', '7.webp', '8.webp', '9.webp', '10.webp'];
    foreach ($imageFiles as $index => $filename) {
        if (file_exists("assets/images/$filename")) {
            $images[] = [
                'id' => $index + 1,
                'filename' => $filename,
                'alt_sk' => 'Krása štúdio OK - interiér',
                'alt_ru' => 'Салон красоты OK - интерьер',
                'alt_ua' => 'Салон краси OK - інтер\'єр',
                'description_sk' => 'Náš krásny salón v centre Bratislavy',
                'description_ru' => 'Наш красивый салон в центре Братиславы',
                'description_ua' => 'Наш красивий салон у центрі Братислави',
            ];
        }
    }
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="pt-32 pb-20 bg-gradient-to-br from-olive-50 to-green-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                <?php echo e(t('gallery_title')); ?>
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
                <?php echo e(t('gallery_subtitle')); ?>
            </p>
        </div>
    </div>
</section>

<!-- Gallery Grid -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($images)): ?>
            <div class="gallery-grid fade-in">
                <?php foreach ($images as $index => $image): ?>
                    <div class="gallery-item" data-index="<?php echo $index; ?>">
                        <img src="assets/images/<?php echo e($image['filename']); ?>" 
                             alt="<?php echo e(getLocalizedField($image, 'alt')); ?>" 
                             loading="lazy"
                             class="w-full h-full object-cover cursor-pointer">
                        
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black/50 opacity-0 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <i class="fas fa-search-plus text-white text-3xl"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Load More Button (if needed) -->
            <?php if (count($images) >= 20): ?>
                <div class="text-center mt-12 fade-in">
                    <button id="load-more" class="btn-primary" data-page="2">
                        <i class="fas fa-plus mr-2"></i>
                        <?php echo CURRENT_LANG === 'sk' ? 'Načítať viac' : (CURRENT_LANG === 'ru' ? 'Загрузить больше' : 'Завантажити більше'); ?>
                    </button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-20 fade-in">
                <i class="fas fa-camera text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-500 mb-4">
                    <?php echo CURRENT_LANG === 'sk' ? 'Galéria sa načítava...' : (CURRENT_LANG === 'ru' ? 'Галерея загружается...' : 'Галерея завантажується...'); ?>
                </h3>
                <p class="text-gray-400">
                    <?php echo CURRENT_LANG === 'sk' ? 'Čoskoro pridáme krásne fotografie nášho štúdia.' : 
                              (CURRENT_LANG === 'ru' ? 'Скоро добавим красивые фотографии нашей студии.' : 
                               'Незабаром додамо красиві фотографії нашої студії.'); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Studio Info -->
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?php echo CURRENT_LANG === 'sk' ? 'Naše priestory' : (CURRENT_LANG === 'ru' ? 'Наши помещения' : 'Наші приміщення'); ?>
            </h2>
            <p class="text-xl text-gray-600">
                <?php echo CURRENT_LANG === 'sk' ? 'Moderné a komfortné prostredie pre vašu pohodu' : 
                          (CURRENT_LANG === 'ru' ? 'Современная и комфортная обстановка для вашего комфорта' : 
                           'Сучасне та комфортне середовище для вашого комфорту'); ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 fade-in">
            <!-- Feature 1 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-olive-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chair text-olive-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Moderné vybavenie' : (CURRENT_LANG === 'ru' ? 'Современное оборудование' : 'Сучасне обладнання'); ?>
                </h3>
                <p class="text-gray-600">
                    <?php echo CURRENT_LANG === 'sk' ? 'Najmodernejšie zariadenie pre všetky naše služby' : 
                              (CURRENT_LANG === 'ru' ? 'Самое современное оборудование для всех наших услуг' : 
                               'Найсучасніше обладнання для всіх наших послуг'); ?>
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-olive-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-olive-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Hygiena a bezpečnosť' : (CURRENT_LANG === 'ru' ? 'Гигиена и безопасность' : 'Гігієна та безпека'); ?>
                </h3>
                <p class="text-gray-600">
                    <?php echo CURRENT_LANG === 'sk' ? 'Najvyššie štandardy čistoty a sterilizácie' : 
                              (CURRENT_LANG === 'ru' ? 'Высочайшие стандарты чистоты и стерилизации' : 
                               'Найвищі стандарти чистоти та стерилізації'); ?>
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-olive-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-music text-olive-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">
                    <?php echo CURRENT_LANG === 'sk' ? 'Relaxačná atmosféra' : (CURRENT_LANG === 'ru' ? 'Расслабляющая атмосфера' : 'Розслаблююча атмосфера'); ?>
                </h3>
                <p class="text-gray-600">
                    <?php echo CURRENT_LANG === 'sk' ? 'Príjemná hudba a pokojná atmosféra pre váš relax' : 
                              (CURRENT_LANG === 'ru' ? 'Приятная музыка и спокойная атмосфера для вашего релакса' : 
                               'Приємна музика та спокійна атмосфера для вашого релаксу'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-olive-600 text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center fade-in">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                <?php echo CURRENT_LANG === 'sk' ? 'Navštívte nás osobne!' : 
                          (CURRENT_LANG === 'ru' ? 'Посетите нас лично!' : 
                           'Відвідайте нас особисто!'); ?>
            </h2>
            <p class="text-xl text-olive-100 mb-8 max-w-3xl mx-auto">
                <?php echo CURRENT_LANG === 'sk' ? 'Príďte sa presvedčiť o kvalite našich služieb a krásnych priestorov.' : 
                          (CURRENT_LANG === 'ru' ? 'Приходите убедиться в качестве наших услуг и красивых помещений.' : 
                           'Прийдіть переконатися в якості наших послуг та красивих приміщень.'); ?>
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.location.href='index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>#booking'" 
                        class="bg-white text-olive-600 hover:bg-gray-100 px-8 py-4 rounded-lg font-semibold transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?php echo e(t('book_now')); ?>
                </button>
                
                <a href="contacts.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" 
                   class="border-2 border-white text-white hover:bg-white hover:text-olive-600 px-8 py-4 rounded-lg font-semibold transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    <?php echo CURRENT_LANG === 'sk' ? 'Ako sa k nám dostať' : (CURRENT_LANG === 'ru' ? 'Как до нас добраться' : 'Як до нас дістатися'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="lightbox" id="lightbox">
    <span class="close">&times;</span>
    <img src="" alt="">
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-4 py-2 rounded">
        <p id="lightbox-description" class="text-sm"></p>
    </div>
</div>

<script>
// Gallery functionality
document.addEventListener('DOMContentLoaded', function() {
    const galleryItems = document.querySelectorAll('.gallery-item img');
    const images = <?php echo json_encode($images); ?>;
    
    galleryItems.forEach((img, index) => {
        img.addEventListener('click', function() {
            openLightbox(index);
        });
    });
    
    function openLightbox(index) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = lightbox.querySelector('img');
        const description = document.getElementById('lightbox-description');
        
        if (images[index]) {
            lightboxImg.src = 'assets/images/' + images[index].filename;
            lightboxImg.alt = images[index].<?php echo 'alt_' . CURRENT_LANG; ?> || 'Krása štúdio OK';
            description.textContent = images[index].<?php echo 'description_' . CURRENT_LANG; ?> || '';
        }
        
        lightbox.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    // Load more functionality
    const loadMoreBtn = document.getElementById('load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', async function() {
            const page = this.dataset.page;
            try {
                const response = await fetch(`api/gallery.php?page=${page}&limit=20`);
                const newImages = await response.json();
                
                if (newImages.length > 0) {
                    // Add new images to gallery
                    const gallery = document.querySelector('.gallery-grid');
                    newImages.forEach((image, index) => {
                        const div = document.createElement('div');
                        div.className = 'gallery-item fade-in';
                        div.innerHTML = `
                            <img src="assets/images/${image.filename}" 
                                 alt="${image.alt}" 
                                 loading="lazy"
                                 class="w-full h-full object-cover cursor-pointer">
                            <div class="absolute inset-0 bg-black/50 opacity-0 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <i class="fas fa-search-plus text-white text-3xl"></i>
                            </div>
                        `;
                        gallery.appendChild(div);
                        
                        // Add click event
                        div.querySelector('img').addEventListener('click', () => {
                            openLightbox(images.length + index);
                        });
                    });
                    
                    // Add new images to array
                    images.push(...newImages);
                    
                    // Update button
                    this.dataset.page = parseInt(page) + 1;
                    
                    if (newImages.length < 20) {
                        this.style.display = 'none';
                    }
                } else {
                    this.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading more images:', error);
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
