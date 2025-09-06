// Main JavaScript file for Krása štúdio OK

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initScrollAnimations();
    initLightbox();
    initAccordion();
    initFormHandlers();
    initCarousel();
    initLazyLoading();
    
    // Update header on scroll
    updateHeaderOnScroll();

    // Load services for booking form
    loadServices();
});

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
    
    // Observe all elements with fade-in class
    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });
}

// Lightbox functionality for gallery
function initLightbox() {
    const galleryItems = document.querySelectorAll('.gallery-item img');
    let lightbox = document.querySelector('.lightbox');
    
    if (!lightbox) {
        // Create lightbox if it doesn't exist
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
    
    // Close lightbox
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
    
    // Close with Escape key
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
            
            // Toggle content
            content.classList.toggle('open');
            
            // Toggle icon rotation
            if (icon) {
                icon.style.transform = content.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
            
            // Close other accordion items
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
    // Booking form
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleBookingForm);
    }
    
    // Contact form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactForm);
    }

    // Initialize Flatpickr for booking date
    const dateInput = document.getElementById('booking-date');
    if (dateInput) {
        fetch('/api/available-dates.php')
            .then(res => res.json())
            .then(dates => {
                console.log('Available dates:', dates); // ✅ логируем даты, полученные от сервера

                flatpickr(dateInput, {
                    minDate: 'today',
                    maxDate: new Date().fp_incr(30),
                    dateFormat: 'Y-m-d',
                    enable: dates,
                    locale: { firstDayOfWeek: 1 },
                    allowInput: true,
                    clickOpens: true, // важно для Safari с readonly полями
                    onChange: function(selectedDates, dateStr, instance) {
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
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="spinner mr-2"></div> Odosielam...';
    
    try {
        // Get reCAPTCHA token
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
            
            // Show success state for booking form
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
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="spinner mr-2"></div> Odosielam...';
    
    try {
        // Get reCAPTCHA token
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
    const bookingSection = document.getElementById('booking');
    if (bookingSection) {
        const successHtml = `
            <div class="max-w-md mx-auto text-center bg-white p-8 rounded-2xl shadow-2xl">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-green-600 text-6xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Rezervácia odoslaná!</h3>
                <p class="text-gray-600 mb-6">Ozýmeme sa vám čoskoro pre potvrdenie termínu.</p>
                <button onclick="resetBookingForm()" class="btn-primary">
                    Rezervovať ďalší termín
                </button>
            </div>
        `;
        
        const bookingContent = bookingSection.querySelector('.booking-content');
        if (bookingContent) {
            bookingContent.innerHTML = successHtml;
        }
    }
}

// Reset booking form
function resetBookingForm() {
    location.reload(); // Simple way to reset the form
}

// Show notification
function showNotification(message, type = 'success') {
    // Remove existing notification
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Hide notification after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Carousel functionality for reviews
function initCarousel() {
    const carousel = document.querySelector('.reviews-carousel');
    if (!carousel) return;
    
    const items = carousel.querySelectorAll('.review-card');
    if (items.length <= 1) return;
    
    let currentIndex = 0;
    const totalItems = items.length;
    
    // Create navigation buttons
    const prevBtn = document.createElement('button');
    prevBtn.className = 'carousel-btn carousel-prev absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 z-10';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left text-gray-600"></i>';
    
    const nextBtn = document.createElement('button');
    nextBtn.className = 'carousel-btn carousel-next absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 z-10';
    nextBtn.innerHTML = '<i class="fas fa-chevron-right text-gray-600"></i>';
    
    const carouselContainer = carousel.parentElement;
    carouselContainer.style.position = 'relative';
    carouselContainer.appendChild(prevBtn);
    carouselContainer.appendChild(nextBtn);
    
    // Navigation functions
    function showSlide(index) {
        items.forEach((item, i) => {
            item.style.display = i === index ? 'block' : 'none';
        });
    }
    
    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalItems;
        showSlide(currentIndex);
    }
    
    function prevSlide() {
        currentIndex = (currentIndex - 1 + totalItems) % totalItems;
        showSlide(currentIndex);
    }
    
    // Event listeners
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    // Initialize
    showSlide(currentIndex);
    
    // Auto-advance carousel
    setInterval(nextSlide, 5000);
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
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers without IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Update header appearance on scroll
function updateHeaderOnScroll() {
    let lastScrollTop = 0;
    const header = document.getElementById('header');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add/remove background
        if (scrollTop > 50) {
            header.classList.add('bg-white/95', 'backdrop-blur-md', 'shadow-md');
            header.classList.remove('bg-transparent');
        } else {
            header.classList.remove('bg-white/95', 'backdrop-blur-md', 'shadow-md');
            header.classList.add('bg-transparent');
        }
        
        lastScrollTop = scrollTop;
    }, { passive: true });
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
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
        
        if (!categorySelect || !serviceSelect) return;
        
        // Populate categories
        const categories = [...new Map(services.map(s => [s.category_id, s])).values()];
        
        categorySelect.innerHTML = '<option value="">Vyberte kategóriu</option>';
        categories.forEach(service => {
            const option = document.createElement('option');
            option.value = service.category_id;
            option.textContent = service.category_name;
            categorySelect.appendChild(option);
        });
        
        // Handle category change
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            serviceSelect.innerHTML = '<option value="">Vyberte službu</option>';
            
            if (categoryId) {
                const categoryServices = services.filter(s => s.category_id === categoryId);
                categoryServices.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = `${service.name} - ${service.price}€`;
                    serviceSelect.appendChild(option);
                });
            }
        });
        
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

// Global variables for reCAPTCHA
let recaptchaSiteKey = window.recaptchaSiteKey || '';

// Export functions for global access
window.smoothScrollTo = smoothScrollTo;
window.loadServices = loadServices;
window.loadTimeSlots = loadTimeSlots;
window.resetBookingForm = resetBookingForm;