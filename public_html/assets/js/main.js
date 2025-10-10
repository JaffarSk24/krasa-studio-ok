// Main JavaScript file for Krása štúdio OK

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initScrollAnimations();
    initLightbox();
    initAccordion();
    initFormHandlers();
    initLazyLoading();
    loadGoogleReviews();
    
    // Update header on scroll
    updateHeaderOnScroll();

    // Load services for booking form
    loadServices();

    // Init WhatsApp links with fallback message
    updateWhatsappLinks();
});

let servicesCache = [];

function buildWhatsappMessageClient(serviceName = '', variant = 'default') {
    const translations = window.translations || {};
    const fallback = translations.whatsapp_fallback_service || 'vybranú službu';
    const serviceText = serviceName || fallback;

    if (variant === 'short' && translations.whatsapp_message_short) {
        // Для короткого варианта используем ровно перевод: не добавляем название услуги.
        return encodeURIComponent(translations.whatsapp_message_short);
    }

    const greeting = translations.whatsapp_greeting || 'Dobrý deň!';
    const template = translations.whatsapp_message_with_service || 'Rada by som si rezervovala službu: :service.';
    const text = `${greeting} ${template.replace(':service', serviceText)}`.trim();
    return encodeURIComponent(text);
}

function updateWhatsappLinks(selectedServiceName = '') {
    // Обновляем ТОЛЬКО кнопки, отмеченные классом js-whatsapp-short
    document.querySelectorAll('.js-whatsapp-short').forEach(link => {
        const number = link.dataset.whatsappNumber || '421915310337';
        const linkServiceName = link.dataset.serviceName || '';
        const serviceName = selectedServiceName || linkServiceName;
        const variant = link.dataset.whatsappVariant || 'short'; // короткий по умолчанию для этой группы
        const encodedMessage = buildWhatsappMessageClient(serviceName, variant);
        link.href = `https://wa.me/${number}?text=${encodedMessage}`;
    });
}

// Scroll animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });
}

// Lightbox functionality for gallery
function initLightbox() {
    const galleryItems = document.querySelectorAll('.gallery-item img');
    let lightbox = document.querySelector('.lightbox');
    
    if (!lightbox) {
        lightbox = document.createElement('div');
        lightbox.className = 'lightbox';
        lightbox.innerHTML = `
            <span class="close">&times;</span>
            <img src="" alt="">
        `;
        document.body.appendChild(lightbox);
    }
    
    const lightboxImg = lightbox.querySelector('img');
    const closeBtn = lightbox.querySelector('.close');
    
    galleryItems.forEach(img => {
        img.addEventListener('click', function() {
            lightboxImg.src = this.src;
            lightboxImg.alt = this.alt;
            lightbox.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });
    
    function closeLightbox() {
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    closeBtn.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });
}

// Accordion functionality for services
function initAccordion() {
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.accordion-icon');
            
            content.classList.toggle('open');
            
            if (icon) {
                icon.style.transform = content.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
            
            accordionHeaders.forEach(otherHeader => {
                if (otherHeader !== this) {
                    const otherContent = otherHeader.nextElementSibling;
                    const otherIcon = otherHeader.querySelector('.accordion-icon');
                    otherContent.classList.remove('open');
                    if (otherIcon) {
                        otherIcon.style.transform = 'rotate(0deg)';
                    }
                }
            });
        });
    });
}

// Form handlers
function initFormHandlers() {
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleBookingForm);
    }
    
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactForm);
    }

    const dateInput = document.getElementById('booking-date');
    if (dateInput) {
        fetch('/api/available-dates.php')
            .then(res => res.json())
            .then(dates => {
                flatpickr(dateInput, {
                    minDate: 'today',
                    maxDate: new Date().fp_incr(30),
                    dateFormat: 'd-m-Y',
                    locale: { firstDayOfWeek: 1 },
                    allowInput: true,
                    clickOpens: true,
                    disable: [
                        function(date) {
                            if (date.getDay() === 0 || date.getDay() === 6) return true;

                            const d = date.getDate().toString().padStart(2, '0');
                            const m = (date.getMonth() + 1).toString().padStart(2, '0');
                            const y = date.getFullYear();
                            const dateStr = `${d}-${m}-${y}`;

                            return !dates.includes(dateStr);
                        }
                    ],
                    onChange: function(selectedDates, dateStr) {
                        if (dateStr) {
                            loadTimeSlots(dateStr);
                        }
                    }
                });
            })
            .catch(err => console.error('Failed to load available dates:', err));
    }
}

// Handle booking form submission
async function handleBookingForm(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="spinner mr-2"></div> Odosielam...';
    
    try {
        const token = await grecaptcha.execute(recaptchaSiteKey, {action: 'booking'});
        
        const formData = new FormData(form);
        formData.append('recaptcha_token', token);
        
        const response = await fetch('api/booking.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || 'Rezervácia bola úspešne odoslaná!', 'success');
            form.reset();
            updateWhatsappLinks();
            showBookingSuccess();
        } else {
            showNotification(result.message || 'Nastala chyba pri odoslaní rezervácie.', 'error');
        }
    } catch (error) {
        console.error('Booking form error:', error);
        showNotification('Nastala chyba pri odoslaní rezervácie.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// Handle contact form submission
async function handleContactForm(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="spinner mr-2"></div> Odosielam...';
    
    try {
        const token = await grecaptcha.execute(recaptchaSiteKey, {action: 'contact'});
        
        const formData = new FormData(form);
        formData.append('recaptcha_token', token);
        
        const response = await fetch('api/contact.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || 'Správa bola úspešne odoslaná!', 'success');
            form.reset();
        } else {
            showNotification(result.message || 'Nastala chyba pri odoslaní správy.', 'error');
        }
    } catch (error) {
        console.error('Contact form error:', error);
        showNotification('Nastala chyba pri odoslaní správy.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// Show booking success state
function showBookingSuccess() {
    let overlay = document.getElementById('booking-success-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'booking-success-overlay';
        overlay.className = 'booking-success-overlay';
        overlay.innerHTML = `
            <button id="booking-success-close" aria-label="Close" class="booking-close-btn">&times;</button>
            <div class="mb-4">
                <i class="fas fa-check-circle text-green-600 text-6xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">${window.translations?.booking_success_title || 'Rezervácia odoslaná!'}</h3>
            <p class="text-gray-600 mb-6">${window.translations?.booking_success_message || 'Ozveme sa vám čoskoro pre potvrdenie termínu.'}</p>
            <button id="booking-success-ok" class="btn-primary">${window.translations?.booking_success_button || 'Rezervovať ďalší termín'}</button>
        `;
        document.body.appendChild(overlay);

        document.body.style.overflow = 'hidden';

        document.getElementById('booking-success-close').addEventListener('click', () => {
            document.body.style.overflow = '';
            overlay.remove();
        });

        document.getElementById('booking-success-ok').addEventListener('click', () => {
            document.body.style.overflow = '';
            overlay.remove();
            location.reload();
        });
    }
}

function resetBookingForm() {
    document.body.style.overflow = '';
    location.reload();
}

// Show notification
function showNotification(message, type = 'success') {
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Lazy loading for images
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Update header appearance on scroll
function updateHeaderOnScroll() {
    const header = document.getElementById('header');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('bg-white/95', 'backdrop-blur-md', 'shadow-md');
            header.classList.remove('bg-transparent');
        } else {
            header.classList.remove('bg-white/95', 'backdrop-blur-md', 'shadow-md');
            header.classList.add('bg-transparent');
        }
    }, { passive: true });
}

// Smooth scroll to element
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Load services for booking form
async function loadServices() {
    try {
        const response = await fetch('api/services.php');
        const services = await response.json();

        const categorySelect = document.getElementById('service-category');
        const serviceSelect = document.getElementById('service-id');
        const currentLang = window.lang || 'sk';

        if (!categorySelect || !serviceSelect) return;

        const normalize = (value) => (value ?? '').toString().trim().toLowerCase();

        let prefillData = null;
        let prefillApplied = false;
        const rawPrefill = sessionStorage.getItem('bookingPrefill');

        if (rawPrefill) {
            try {
                prefillData = JSON.parse(rawPrefill);
            } catch (err) {
                console.warn('Unable to parse bookingPrefill payload', err);
            }
            sessionStorage.removeItem('bookingPrefill');
        }

        if (prefillData && prefillData.lang && prefillData.lang !== currentLang) {
            prefillData = null;
        }

        const categoriesMap = new Map();
        services.forEach((service) => {
            if (!categoriesMap.has(service.category_id)) {
                categoriesMap.set(service.category_id, {
                    id: service.category_id,
                    name: service[`category_name_${currentLang}`] || service.category_name_sk || 'Kategória',
                });
            }
        });

        categorySelect.innerHTML = `<option value="">${window.translations?.select_category || 'Vyberte kategóriu'}</option>`;
        serviceSelect.innerHTML = `<option value="">${window.translations?.select_service || 'Vyberte službu'}</option>`;

        categoriesMap.forEach((category) => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });

        const tryApplyServicePrefill = (servicesForCategory) => {
            if (!prefillData || prefillApplied) return;

            const byId = servicesForCategory.find(
                (srv) => String(srv.id) === String(prefillData.serviceId)
            );

            const byName = servicesForCategory.find((srv) =>
                normalize(srv[`name_${currentLang}`] || srv.name_sk) === normalize(prefillData.serviceName)
            );

            const matchedService = byId || byName;

            if (matchedService) {
                serviceSelect.value = matchedService.id;
                serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
                prefillApplied = true;
            }
        };

        categorySelect.addEventListener('change', function () {
            const categoryId = this.value;
            serviceSelect.innerHTML = `<option value="">${window.translations?.select_service || 'Vyberte službu'}</option>`;

            if (!categoryId) return;

            const servicesForCategory = services.filter(
                (srv) => String(srv.category_id) === String(categoryId)
            );

            servicesForCategory.forEach((srv) => {
                const option = document.createElement('option');
                const serviceName = srv[`name_${currentLang}`] || srv.name_sk || 'Služба';
                option.value = srv.id;
                option.textContent = `${serviceName} - ${srv.price}€`;
                serviceSelect.appendChild(option);
            });

            if (
                prefillData &&
                (String(prefillData.categoryId) === String(categoryId) ||
                    normalize(prefillData.categoryName) === normalize(this.options[this.selectedIndex]?.textContent))
            ) {
                tryApplyServicePrefill(servicesForCategory);
            }
        });

        const resolvePrefillCategory = () => {
            if (!prefillData) return null;

            if (
                prefillData.categoryId &&
                categorySelect.querySelector(`option[value="${prefillData.categoryId}"]`)
            ) {
                return prefillData.categoryId;
            }

            if (prefillData.serviceId) {
                const match = services.find(
                    (srv) => String(srv.id) === String(prefillData.serviceId)
                );
                if (match) {
                    const option = categorySelect.querySelector(`option[value="${match.category_id}"]`);
                    if (option) {
                        prefillData.categoryId = match.category_id;
                        return match.category_id;
                    }
                }
            }

            if (prefillData.categoryName) {
                const target = normalize(prefillData.categoryName);
                const option = Array.from(categorySelect.options).find(
                    (opt) => normalize(opt.textContent) === target
                );
                if (option) {
                    prefillData.categoryId = option.value;
                    return option.value;
                }
            }

            return null;
        };

        const targetCategory = resolvePrefillCategory();

        if (targetCategory) {
            categorySelect.value = targetCategory;
            categorySelect.dispatchEvent(new Event('change', { bubbles: true }));
            setTimeout(() => {
                if (!prefillApplied && prefillData) {
                    const servicesForCategory = services.filter(
                        (srv) => String(srv.category_id) === String(categorySelect.value)
                    );
                    tryApplyServicePrefill(servicesForCategory);
                }
            }, 150);
        } else {
            prefillData = null;
        }
    } catch (error) {
        console.error('Error loading services:', error);
    }
}

// Load time slots for selected date
function loadTimeSlots(date) {
    const timeSelect = document.getElementById('booking-time');
    if (!timeSelect) return;

    timeSelect.innerHTML = '<option value="">' + (window.translations?.select_time || 'Vyberte čas') + '</option>';

    fetch('/api/time-slots.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ date: date })
    })
    .then(res => res.json())
    .then(slots => {
        if (Array.isArray(slots) && slots.length) {
            slots.forEach(slot => {
                const opt = document.createElement('option');
                opt.value = slot.time;
                opt.textContent = slot.time;
                timeSelect.appendChild(opt);
            });
        } else {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = window.translations?.time_not_available || 'Nie sú dostupné žiadne časy';
            timeSelect.appendChild(opt);
        }
    })
    .catch(err => {
        console.error('Failed to load time slots:', err);
    });
}

// ========== Google Reviews (Place Details + Swiper) ==========

// Настройки
const GOOGLE_PLACE_ID = 'ChIJURI_hqWPbEcRdG8m3Yd9dZs';
const REVIEWS_LIMIT   = 12;               
const MIN_RATING      = 4;                
const CACHE_KEY       = 'gr_cache_v1';
const CACHE_TTL_MS    = 7 * 24 * 60 * 60 * 1000; // 7 дней

function fetchWithTimeout(url, options = {}, timeoutMs = 6000) {
  return Promise.race([
    fetch(url, options),
    new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), timeoutMs))
  ]);
}

// Безопасная инициализация Swiper
function initReviewsSwiper(slidesAdded) {
  const options = {
    slidesPerView: 3,
    spaceBetween: 24,
    watchOverflow: false,
    autoHeight: false,
    loop: slidesAdded > 3,
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    keyboard: { enabled: true },
    breakpoints: {
      320:  { slidesPerView: 1, spaceBetween: 12 },
      640:  { slidesPerView: 2, spaceBetween: 16 },
      1024: { slidesPerView: 3, spaceBetween: 24 }
    }
  };
  const arrows = document.querySelectorAll('.swiper-button-prev, .swiper-button-next');
  arrows.forEach(a => a.style.display = 'inline-flex');

  if (window.Swiper) {
    const sw = new Swiper('.reviews-swiper', options);
    requestAnimationFrame(() => sw.update());
  } else {
    arrows.forEach(a => a.style.display = 'none');
    const w = document.getElementById('google-reviews-wrapper');
    if (w) {
      w.style.display = 'grid';
      w.style.gridTemplateColumns = '1fr';
      w.style.gap = '16px';
    }
    console.warn('Swiper JS не найден — показываю список без слайдера.');
  }
}

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function renderStars(rating) {
  const full = Math.round(Number(rating) || 0);
  return Array.from({length:5}, (_,i) =>
    `<i class="fas fa-star ${i < full ? 'text-yellow-400' : 'text-gray-300'}"></i>`
  ).join('');
}

function setSummary(place) {
  const wrap = document.getElementById('google-reviews-summary');
  if (!wrap || !place) return;

  const nameEl   = document.getElementById('google-reviews-place-name');
  const ratingEl = document.getElementById('google-reviews-rating-number');
  const starsEl  = document.getElementById('google-reviews-stars');
  const totalEl  = document.getElementById('google-reviews-total');
  const urlA     = document.getElementById('google-reviews-place-url');
  const iconImg  = document.getElementById('place-icon');
  const iconWrap = document.getElementById('place-icon-wrap');

  if (nameEl) nameEl.textContent = place.name || '';
  const r = Number(place.rating || 0);
  if (ratingEl) ratingEl.textContent = r.toFixed(1);
  if (starsEl) starsEl.innerHTML = renderStars(r);
  if (totalEl) totalEl.textContent = `${place.user_ratings_total || place.total || 0} ${window.lang==='ru'?'отзывов':window.lang==='ua'?'відгуків':'recenzií'}`;
  if (urlA && place.url) urlA.href = place.url;

  // icon_mask_base_uri + background_color
  // Если у Google вернётся icon_mask_base_uri, используем его, иначе icon или дефолт
  const mask = place.icon_mask_base_uri;
  const bg   = place.icon_background_color || place.icon_background_color || '#e5e7eb';
  const icon = place.icon || 'https://maps.gstatic.com/mapfiles/place_api/icons/v1/png_71/generic_business-71.png';

  if (iconWrap) iconWrap.style.background = bg || '#e5e7eb';
  if (iconImg) {
    iconImg.src = mask ? `${mask}.png` : icon;
    iconImg.alt = place.name || 'Place';
    iconImg.classList.add('object-contain');
  }

  wrap.classList.remove('hidden');
}

// Загрузка Google Reviews (простая сетка без Swiper)
async function loadGoogleReviews() {
  const container = document.getElementById('google-reviews');
  if (!container) return;

  // Кэш
  try {
    const raw = localStorage.getItem(CACHE_KEY);
    if (raw) {
      const { ts, data } = JSON.parse(raw);
      if (Date.now() - ts < CACHE_TTL_MS && data?.result) {
        setSummary(data.result);
        renderReviews(data.result.reviews || []);
        return;
      }
    }
  } catch {}

  const url = `/api/google-places-proxy.php?place_id=${encodeURIComponent(GOOGLE_PLACE_ID)}&language=${encodeURIComponent(window.lang||'sk')}`;

  try {
    const res = await fetchWithTimeout(url, { cache: 'no-store' }, 6000);
    const json = await res.json();
    if (json?.result) {
      try { localStorage.setItem(CACHE_KEY, JSON.stringify({ ts: Date.now(), data: json })); } catch {}
      setSummary(json.result);
      renderReviews(json.result.reviews || []);
    } else {
      throw new Error('Empty result');
    }
  } catch (e) {
    console.warn('Failed to load Google Reviews:', e);
    const placeholder = document.getElementById('google-reviews-placeholder');
    if (placeholder) {
      placeholder.textContent = window.translations?.no_reviews || 'Reviews will appear soon.';
    }
  }
}

function renderReviews(reviews) {
  const container = document.getElementById('google-reviews');
  const placeholder = document.getElementById('google-reviews-placeholder');
  if (!container) return;
  
  // Очищаем контейнер от старых карточек
  container.innerHTML = '';
  
  const filtered = (reviews || []).filter(r => Number(r.rating) >= MIN_RATING).slice(0, 5);

  filtered.forEach(r => {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-3xl shadow-lg p-6';
    card.innerHTML = `
      <div class="flex items-center gap-3 mb-4">
        <img src="${esc(r.profile_photo_url||'')}" class="w-12 h-12 rounded-full object-cover" onerror="this.style.display='none'">
        <div>
          <div class="font-semibold text-gray-900">${esc(r.author_name||'')}</div>
          <div class="text-xs text-gray-500">${esc(r.relative_time_description||'')}</div>
        </div>
      </div>
      <div class="text-yellow-400 text-sm mb-3">${renderStars(r.rating)}</div>
      <p class="text-sm text-gray-700 leading-relaxed">${esc((r.text||'').substring(0, 180))}${r.text?.length > 180 ? '...' : ''}</p>
    `;
    container.appendChild(card);
  });
}

document.addEventListener('DOMContentLoaded', loadGoogleReviews);

// Helpers
function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function(m) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m];
    });
}

// Global variables for reCAPTCHA
let recaptchaSiteKey = window.recaptchaSiteKey || '';

window.smoothScrollTo = smoothScrollTo;
window.loadServices = loadServices;
window.loadTimeSlots = loadTimeSlots;
window.resetBookingForm = resetBookingForm;