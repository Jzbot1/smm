        </main>
    </div>
    <script>
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');
        const sidebar = document.getElementById('mobile-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');

        function toggleSidebar() {
            sidebar?.classList.toggle('-translate-x-full');
            backdrop?.classList.toggle('hidden');
        }

        openBtn?.addEventListener('click', toggleSidebar);
        closeBtn?.addEventListener('click', toggleSidebar);
        backdrop?.addEventListener('click', toggleSidebar);
        
        // Auto-close on resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar?.classList.add('-translate-x-full');
                backdrop?.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
