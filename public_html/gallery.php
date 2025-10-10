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
                        <?php echo e(t('gallery_load_more')); ?>
                    </button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-20 fade-in">
                <i class="fas fa-camera text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-500 mb-4">
                    <?php echo e(t('gallery_loading_title')); ?>
                </h3>
                <p class="text-gray-400">
                    <?php echo e(t('gallery_loading_text')); ?>
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
                <?php echo e(t('gallery_spaces_title')); ?>
            </h2>
            <p class="text-xl text-gray-600">
                <?php echo e(t('gallery_spaces_subtitle')); ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 fade-in">
            <!-- Feature 1 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-olive-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chair text-olive-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">
                    <?php echo e(t('gallery_feature_equipment_title')); ?>
                </h3>
                <p class="text-gray-600">
                    <?php echo e(t('gallery_feature_equipment_text')); ?>
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-olive-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-olive-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">
                    <?php echo e(t('gallery_feature_safety_title')); ?>
                </h3>
                <p class="text-gray-600">
                    <?php echo e(t('gallery_feature_safety_text')); ?>
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-olive-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-music text-olive-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">
                    <?php echo e(t('gallery_feature_atmosphere_title')); ?>
                </h3>
                <p class="text-gray-600">
                    <?php echo e(t('gallery_feature_atmosphere_text')); ?>
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
                <?php echo e(t('gallery_cta_title')); ?>
            </h2>
            <p class="text-xl text-olive-100 mb-8 max-w-3xl mx-auto">
                <?php echo e(t('gallery_cta_text')); ?>
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
                    <?php echo e(t('gallery_cta_route_button')); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-50">
    <button id="lightbox-close" type="button" class="absolute top-6 right-6 text-white text-3xl font-light focus:outline-none">
        &times;
    </button>
    <button id="lightbox-prev" type="button" class="absolute left-6 top-1/2 -translate-y-1/2 text-white text-3xl font-light focus:outline-none px-3 py-2 bg-white/10 rounded-full hover:bg-white/20">
        <i class="fas fa-chevron-left"></i>
    </button>
    <img id="lightbox-image" src="" alt="" class="max-h-[80vh] max-w-[90vw] rounded-xl object-contain shadow-2xl">
    <button id="lightbox-next" type="button" class="absolute right-6 top-1/2 -translate-y-1/2 text-white text-3xl font-light focus:outline-none px-3 py-2 bg-white/10 rounded-full hover:bg-white/20">
        <i class="fas fa-chevron-right"></i>
    </button>
    <div id="lightbox-caption" class="absolute bottom-8 left-1/2 -translate-x-1/2 text-white bg-black/60 px-4 py-2 rounded-lg text-sm md:text-base"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const imagesData = <?php echo json_encode($images); ?>;
    const altField = '<?php echo 'alt_' . CURRENT_LANG; ?>';
    const descField = '<?php echo 'description_' . CURRENT_LANG; ?>';

    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxCaption = document.getElementById('lightbox-caption');
    const closeBtn = document.getElementById('lightbox-close');
    const prevBtn = document.getElementById('lightbox-prev');
    const nextBtn = document.getElementById('lightbox-next');
    const loadMoreBtn = document.getElementById('load-more');
    const galleryGrid = document.querySelector('.gallery-grid');

    let currentIndex = 0;

    const getAlt = (image) =>
        image?.[altField] || image?.alt || 'Krása štúdio OK';

    const getDescription = (image) =>
        image?.[descField] || image?.description || '';

    function toggleArrows() {
        const hasMultiple = imagesData.length > 1;
        prevBtn.classList.toggle('hidden', !hasMultiple);
        nextBtn.classList.toggle('hidden', !hasMultiple);
    }

    function showImage(index) {
        const image = imagesData[index];
        if (!image) return;

        lightboxImage.src = 'assets/images/' + image.filename;
        lightboxImage.alt = getAlt(image);
        const description = getDescription(image);
        lightboxCaption.textContent = description || getAlt(image);
        toggleArrows();
    }

    function openLightbox(index) {
        currentIndex = index;
        showImage(currentIndex);
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        document.body.style.overflow = '';
        lightboxImage.src = '';
        lightboxImage.alt = '';
        lightboxCaption.textContent = '';
    }

    function gotoPrev() {
        if (!imagesData.length) return;
        currentIndex = (currentIndex - 1 + imagesData.length) % imagesData.length;
        showImage(currentIndex);
    }

    function gotoNext() {
        if (!imagesData.length) return;
        currentIndex = (currentIndex + 1) % imagesData.length;
        showImage(currentIndex);
    }

    function attachLightboxHandler(element, index) {
        element.addEventListener('click', () => openLightbox(index));
    }

    document
        .querySelectorAll('.gallery-item')
        .forEach((item, index) => attachLightboxHandler(item, index));

    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', gotoPrev);
    nextBtn.addEventListener('click', gotoNext);

    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (lightbox.classList.contains('hidden')) return;

        if (event.key === 'Escape') {
            closeLightbox();
        } else if (event.key === 'ArrowLeft') {
            gotoPrev();
        } else if (event.key === 'ArrowRight') {
            gotoNext();
        }
    });

    if (loadMoreBtn && galleryGrid) {
        loadMoreBtn.addEventListener('click', async () => {
            const page = loadMoreBtn.dataset.page;

            try {
                const response = await fetch(`api/gallery.php?page=${page}&limit=20`);
                const newImages = await response.json();

                if (Array.isArray(newImages) && newImages.length) {
                    newImages.forEach((image) => {
                        const newIndex = imagesData.push(image) - 1;

                        const wrapper = document.createElement('div');
                        wrapper.className = 'gallery-item fade-in';
                        wrapper.dataset.index = newIndex;

                        const img = document.createElement('img');
                        img.src = 'assets/images/' + image.filename;
                        img.alt = getAlt(image);
                        img.loading = 'lazy';
                        img.className = 'w-full h-full object-cover cursor-pointer';

                        const overlay = document.createElement('div');
                        overlay.className = 'absolute inset-0 bg-black/50 opacity-0 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center';
                        overlay.innerHTML = '<i class="fas fa-search-plus text-white text-3xl"></i>';

                        wrapper.appendChild(img);
                        wrapper.appendChild(overlay);
                        galleryGrid.appendChild(wrapper);

                        attachLightboxHandler(wrapper, newIndex);
                    });

                    loadMoreBtn.dataset.page = String(parseInt(page, 10) + 1);

                    if (newImages.length < 20) {
                        loadMoreBtn.style.display = 'none';
                    }
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading more images:', error);
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>