<div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Mes Annonces</h1>
            <a href="/room/create" class="bg-red-400 text-white px-4 py-2 rounded-full hover:bg-red-500 transition">
                Ajouter un bien
            </a>
        </div>

        <?php if (!empty($rooms)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rooms as $room): ?>
                    <div class="bg-white rounded-xl shadow p-4">
                        <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($room['title']) ?></h2>
                        <p class="text-gray-600 mb-1"><?= htmlspecialchars($room['city']) ?>, <?= htmlspecialchars($room['country']) ?></p>
                        <p class="text-gray-800 font-bold mb-2"><?= htmlspecialchars($room['price_per_night']) ?>€ / nuit</p>
                        <p class="text-gray-700 text-sm"><?= htmlspecialchars($room['description']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600 mt-4">Vous n'avez pas encore de biens. Cliquez sur "Ajouter un bien" pour commencer.</p>
        <?php endif; ?>
    </div>
</div>
