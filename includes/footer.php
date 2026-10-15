<?php
// includes/footer.php
$root_prefix = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : '';
?>
<!-- FOOTER -->
<footer class="bg-white border-t-4 border-primary mt-16">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Brand -->
            <div>
                <div class="flex items-center gap-2 text-primary font-black text-2xl mb-3">
                    <span class="text-3xl">⭐</span>
                    <span>Koding<span class="text-accent">Kids</span></span>
                </div>
                <p class="text-gray-500 text-sm">Platform belajar coding yang seru dan menyenangkan untuk anak SD!</p>
                <div class="flex gap-3 mt-4 text-2xl">
                    <span title="Fun">🎮</span>
                    <span title="Belajar">📚</span>
                    <span title="Kreatif">🎨</span>
                    <span title="Pintar">🧠</span>
                </div>
            </div>
            <!-- Links -->
            <div>
                <h4 class="font-bold text-gray-700 mb-3">📌 Menu</h4>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li><a href="<?= $root_prefix ?>index.php" class="hover:text-primary">🏠 Beranda</a></li>
                    <li><a href="<?= $root_prefix ?>pages/materi.php" class="hover:text-primary">📚 Materi Belajar</a></li>
                    <li><a href="<?= $root_prefix ?>pages/leaderboard.php" class="hover:text-primary">🏆 Leaderboard</a></li>
                    <li><a href="<?= $root_prefix ?>pages/dashboard.php" class="hover:text-primary">📊 Dashboard</a></li>
                </ul>
            </div>
            <!-- Info -->
            <div>
                <h4 class="font-bold text-gray-700 mb-3">ℹ️ Info</h4>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li>🎯 Untuk anak SD kelas 1-6</li>
                    <li>📖 5 Materi tersedia</li>
                    <li>🧩 20+ Soal Kuis</li>
                    <li>🏅 5 Badge eksklusif</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-200 mt-8 pt-6 text-center text-sm text-gray-400">
            <p>© 2026 KodingKids · Dibuat dengan ❤️ untuk anak-anak Indonesia</p>
            <p class="mt-1">M-ONE Telkomsel Coding Competition 2026</p>
        </div>
    </div>
</footer>

<script src="<?= $root_prefix ?>assets/js/main.js"></script>
</body>
</html>