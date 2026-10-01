</main>

<footer class="bg-white border-t border-stone-300">
    <div class="max-w-6xl mx-auto px-4 py-6 text-sm text-stone-600 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
        <p class="flex items-center gap-2"><?= icon('package', 'w-4 h-4') ?> Re-Use: a localized portal for donating and claiming used equipment.</p>
        <p>NGO and NPO communities.</p>
    </div>
</footer>

<script>
    const btn = document.getElementById('menuBtn');
    const menu = document.getElementById('mobileMenu');
    btn.addEventListener('click', () => {
        const open = menu.classList.toggle('hidden') === false;
        document.getElementById('iconOpen').classList.toggle('hidden', open);
        document.getElementById('iconClose').classList.toggle('hidden', !open);
        btn.setAttribute('aria-expanded', open);
    });
</script>
</body>
</html>
