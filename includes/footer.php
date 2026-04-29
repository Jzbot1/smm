        </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-6 left-4 right-4 z-[100] glass border border-slate-700/50 rounded-2xl px-2 py-2 flex justify-around items-center shadow-2xl safe-area-inset-bottom">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']);
        $nav_links = [
            ['index.php', 'fas fa-plus-circle', 'Order'],
            ['orders.php', 'fas fa-shopping-basket', 'Orders'],
            ['services.php', 'fas fa-list-ul', 'Services'],
            ['add_funds.php', 'fas fa-wallet', 'Funds'],
            ['profile.php', 'fas fa-user-circle', 'Profile']
        ];
        foreach ($nav_links as $link):
            $active = ($current_page == $link[0]) ? 'text-indigo-400 bg-indigo-500/10' : 'text-slate-400';
        ?>
            <a href="<?php echo BASE_URL; ?>/<?php echo $link[0]; ?>" class="flex flex-col items-center justify-center w-14 h-14 rounded-xl transition-all duration-300 <?php echo $active; ?>">
                <i class="<?php echo $link[1]; ?> text-xl"></i>
                <span class="text-[9px] mt-1 font-bold uppercase tracking-wider"><?php echo $link[2]; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Footer -->
    <footer class="glass border-t border-slate-700/50 mt-auto pb-24 md:pb-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <h2 class="text-xl font-bold text-white mb-4"><?php echo htmlspecialchars($site_name); ?></h2>
                    <p class="text-slate-400 text-sm max-w-sm mb-6">
                        Welcome to <strong><?php echo htmlspecialchars($site_name); ?></strong>. The world's leading SMM Panel providing high-quality social media marketing services at the most affordable rates. Boost your presence today!
                    </p>
                    <div class="flex space-x-4">
                        <?php if (!empty($site_settings['facebook_url'])): ?>
                            <a href="<?php echo htmlspecialchars($site_settings['facebook_url']); ?>" target="_blank" class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-all"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['twitter_url'])): ?>
                            <a href="<?php echo htmlspecialchars($site_settings['twitter_url']); ?>" target="_blank" class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-all"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['instagram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($site_settings['instagram_url']); ?>" target="_blank" class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-all"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['telegram_url'])): ?>
                            <a href="<?php echo htmlspecialchars($site_settings['telegram_url']); ?>" target="_blank" class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-all"><i class="fab fa-telegram-plane"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="<?php echo BASE_URL; ?>/index" class="hover:text-indigo-400 transition-colors">New Order</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/services" class="hover:text-indigo-400 transition-colors">Services List</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/api_docs" class="hover:text-indigo-400 transition-colors">API Documentation</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/orders" class="hover:text-indigo-400 transition-colors">Order History</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="<?php echo BASE_URL; ?>/tickets" class="hover:text-indigo-400 transition-colors">Tickets</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Privacy Policy</a></li>
                        <?php if ($support_number): ?>
                            <li class="text-indigo-400 font-medium"><?php echo htmlspecialchars($support_number); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-700/30 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-slate-500">
                <div class="mb-4 md:mb-0 text-center md:text-left">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
                    <p class="mt-1">
                        Designed and Developed by <a href="https://wa.me/918730063275" target="_blank" class="text-indigo-400 hover:text-indigo-300 transition-colors font-semibold">Zomuana Sailo</a>
                    </p>
                </div>
                <div class="flex space-x-4">
                    <i class="fab fa-cc-visa text-xl"></i>
                    <i class="fab fa-cc-mastercard text-xl"></i>
                    <i class="fab fa-google-pay text-xl"></i>
                    <i class="fab fa-apple-pay text-xl"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- PWA Install Modal (Force-like) -->
    <div id="pwa-install-banner" class="fixed inset-0 z-[150] flex items-end md:items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden">
        <div class="max-w-sm w-full glass border border-indigo-500/30 rounded-3xl p-8 shadow-2xl text-center relative overflow-hidden animate-in slide-in-from-bottom duration-500">
            <div class="absolute -top-12 -right-12 w-24 h-24 bg-indigo-600/20 rounded-full blur-2xl"></div>
            
            <div class="w-20 h-20 bg-indigo-600 rounded-3xl p-5 shadow-2xl shadow-indigo-600/40 mx-auto mb-6 flex items-center justify-center">
                <i class="fas fa-rocket text-white text-4xl"></i>
            </div>
            
            <h4 class="text-white font-black text-2xl mb-2">Boost Your Experience</h4>
            <p class="text-slate-400 text-sm mb-8 leading-relaxed">Install our App for faster access, real-time order tracking, and exclusive discounts.</p>
            
            <div class="flex flex-col space-y-3">
                <button id="pwa-install-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-600/20 transition-all active:scale-95">
                    Install App
                </button>
                <button id="pwa-close-btn" class="w-full text-slate-500 hover:text-slate-300 text-xs font-bold py-2 uppercase tracking-widest transition-colors">
                    Continue in Browser
                </button>
            </div>
        </div>
    </div>

    <!-- Push Notification Prompt (Hidden by default) -->
    <div id="push-prompt" class="fixed top-4 left-1/2 -translate-x-1/2 z-[110] w-full max-w-sm px-4 hidden">
        <div class="glass border border-indigo-500/30 rounded-2xl p-4 shadow-2xl flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-indigo-500/20 text-indigo-400 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-bell text-xl animate-bounce"></i>
            </div>
            <h4 class="text-white font-bold mb-1 text-sm">Stay Updated!</h4>
            <p class="text-slate-400 text-xs mb-4">Enable push notifications for order updates and exclusive offers.</p>
            <div class="flex space-x-3 w-full">
                <button id="push-allow-btn" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2.5 rounded-xl transition-all">
                    Enable
                </button>
                <button id="push-deny-btn" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold py-2.5 rounded-xl transition-all">
                    Later
                </button>
            </div>
        </div>
    </div>

    <script>
        // PWA Registration and Logic
        let deferredPrompt;
        const installBanner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const closeBtn = document.getElementById('pwa-close-btn');

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo BASE_URL; ?>/sw.js')
                    .then(reg => console.log('PWA: Service Worker Registered'))
                    .catch(err => console.error('PWA: Service Worker Registration failed', err));
            });
        }

        function showInstallModal() {
            if (!window.matchMedia('(display-mode: standalone)').matches) {
                console.log('PWA: Showing Install Modal');
                installBanner.classList.remove('hidden');
                installBanner.classList.add('flex');
            } else {
                console.log('PWA: Already in standalone mode');
            }
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('PWA: beforeinstallprompt fired');
            e.preventDefault();
            deferredPrompt = e;
            showInstallModal();
        });

        // Forced display for iOS or if prompt hasn't fired
        setTimeout(() => {
            if (!window.matchMedia('(display-mode: standalone)').matches && !localStorage.getItem('pwa_dismissed')) {
                console.log('PWA: Forced check');
                showInstallModal();
            }
        }, 3000);

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`PWA: User choice: ${outcome}`);
                if (outcome === 'accepted') {
                    installBanner.classList.add('hidden');
                    installBanner.classList.remove('flex');
                }
                deferredPrompt = null;
            } else {
                // Fallback for iOS or other browsers
                alert('To install: Tap the "Share" icon and then "Add to Home Screen"');
                installBanner.classList.add('hidden');
                installBanner.classList.remove('flex');
            }
        });

        closeBtn.addEventListener('click', () => {
            installBanner.classList.add('hidden');
            installBanner.classList.remove('flex');
            // Don't show again in this session
            localStorage.setItem('pwa_dismissed', 'true');
        });

        // Push Notification Logic
        const pushPrompt = document.getElementById('push-prompt');
        const pushAllowBtn = document.getElementById('push-allow-btn');
        const pushDenyBtn = document.getElementById('push-deny-btn');

        function checkNotificationPermission() {
            if (!("Notification" in window)) return;
            
            if (Notification.permission === "default" && !localStorage.getItem('push_prompted')) {
                setTimeout(() => {
                    pushPrompt.classList.remove('hidden');
                }, 5000);
            }
        }

        pushAllowBtn.addEventListener('click', () => {
            Notification.requestPermission().then(permission => {
                pushPrompt.classList.add('hidden');
                localStorage.setItem('push_prompted', 'true');
                if (permission === "granted") {
                    new Notification("Welcome!", {
                        body: "You'll now receive updates directly on your device.",
                        icon: "<?php echo BASE_URL; ?>/assets/images/icon-192.png"
                    });
                }
            });
        });

        pushDenyBtn.addEventListener('click', () => {
            pushPrompt.classList.add('hidden');
            localStorage.setItem('push_prompted', 'true');
        });

        // Initialize notification check
        checkNotificationPermission();

        // Active link highlighting
        document.querySelectorAll('nav a').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('bg-slate-800', 'text-white');
                link.classList.remove('text-slate-300');
            }
        });
    </script>
</body>
</html>
