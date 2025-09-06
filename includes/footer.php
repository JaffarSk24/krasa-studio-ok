
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-12">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Logo and About -->
                    <div class="lg:col-span-2">
                        <a href="index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>" class="flex items-center space-x-3 mb-4">
                            <div class="relative w-12 h-12">
                                <img src="assets/images/Mini Логотип без фона.png" alt="Krása štúdio OK" class="w-full h-full object-contain">
                            </div>
                            <span class="font-bold text-xl text-olive-600">Krása štúdio "OK"</span>
                        </a>
                        <p class="text-gray-600 mb-6 max-w-md">
                            <?php echo t('footer_description'); ?>
                        </p>
                        
                        <!-- WhatsApp Button -->
                        <?php 
                        $whatsappNumber = '+421905123456';
                        $whatsappMessage = urlencode(t('whatsapp_message_default'));
                        ?>
                        <a href="https://wa.me/<?php echo str_replace('+', '', $whatsappNumber); ?>?text=<?php echo $whatsappMessage; ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                            <i class="fab fa-whatsapp mr-2 text-lg"></i>
                            WhatsApp
                        </a>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4"><?php echo t('contact_info'); ?></h3>
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-phone text-olive-600 mr-3"></i>
                                <span>+421 905 123 456</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope text-olive-600 mr-3"></i>
                                <span>info@krasastudio.sk</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-map-marker-alt text-olive-600 mr-3"></i>
                                <span>Bratislava, Slovensko</span>
                            </div>
                        </div>
                    </div>

                    <!-- Opening Hours -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4"><?php echo t('opening_hours'); ?></h3>
                        <div class="space-y-2 text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-olive-600 mr-3"></i>
                                <span><?php echo t('hours_schedule'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bottom Section -->
                <div class="border-t border-gray-200 pt-8 mt-8">
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <p class="text-gray-600 text-sm">
                            &copy; <?php echo date('Y'); ?> Krása štúdio "OK". Všetky práva vyhradené.
                        </p>
                        
                        <!-- Social Links -->
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-600 hover:text-olive-600 transition-colors duration-200">
                                <i class="fab fa-facebook text-lg"></i>
                            </a>
                            <a href="#" class="text-gray-600 hover:text-olive-600 transition-colors duration-200">
                                <i class="fab fa-instagram text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // Language switcher
        function changeLanguage(lang) {
            const url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }
        
        // Scroll to booking section
        function scrollToBooking() {
            const bookingSection = document.getElementById('booking');
            if (bookingSection) {
                bookingSection.scrollIntoView({ behavior: 'smooth' });
            } else {
                // If not on home page, redirect to home page with booking anchor
                window.location.href = 'index.php<?php echo CURRENT_LANG !== DEFAULT_LANGUAGE ? '?lang=' . CURRENT_LANG : ''; ?>#booking';
            }
        }
        
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            const icon = this.querySelector('i');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.className = 'fas fa-times text-lg';
            } else {
                menu.classList.add('hidden');
                icon.className = 'fas fa-bars text-lg';
            }
        });
        
        // Header background on scroll
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 10) {
                header.classList.remove('bg-transparent');
                header.classList.add('bg-white', 'shadow-md', 'backdrop-blur-md', 'bg-opacity-95');
            } else {
                header.classList.add('bg-transparent');
                header.classList.remove('bg-white', 'shadow-md', 'backdrop-blur-md', 'bg-opacity-95');
            }
        });
    </script>
    
    <?php include_once 'body-extra.php'; ?>
</body>
</html>
