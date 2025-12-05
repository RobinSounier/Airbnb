<?php
// On inclut la navbar
require dirname(__FILE__) . '/../_templates/_navbar.html.php';

// Assurez-vous que $rooms et $allEquipments sont définis, même à vide
$rooms = $rooms ?? [];
$allEquipments = $allEquipments ?? [];
$currentFilters = $currentFilters ?? [];
?>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        <form method="GET" action="/" class="bg-white shadow-md rounded-xl p-6 mb-8 border border-gray-100">

            <div class="flex justify-between items-center cursor-pointer mb-4" id="filter-header">
                <h2 class="text-xl font-bold text-gray-800">Filtrer les annonces</h2>
                <button type="button" class="text-gray-500 hover:text-gray-700 transition" id="filter-toggle-btn">
                    <svg id="toggle-icon" class="w-6 h-6 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <div id="filter-content" class="space-y-6 hidden">

                <div class="grid grid-cols-2 md:grid-cols-6 gap-4 items-end">

                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700">Pays</label>
                        <input type="text" name="country" id="country" value="<?= htmlspecialchars($currentFilters['country'] ?? '') ?>" class="mt-1 w-full border border-gray-300 rounded-md shadow-sm h-10 px-3">
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">Ville</label>
                        <input type="text" name="city" id="city" value="<?= htmlspecialchars($currentFilters['city'] ?? '') ?>" class="mt-1 w-full border border-gray-300 rounded-md shadow-sm h-10 px-3">
                    </div>

                    <div>
                        <label for="room_type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="room_type" id="room_type" class="mt-1 w-full border border-gray-300 rounded-md shadow-sm h-10 px-3">
                            <option value="">Tous les types</option>
                            <?php
                            $types = ['Appartement', 'Maison', 'Studio', 'Villa', 'Chalet', 'Loft'];
                            foreach ($types as $type):
                                ?>
                                <option value="<?= strtolower($type) ?>" <?= (($currentFilters['roomType'] ?? '') === strtolower($type)) ? 'selected' : '' ?>>
                                    <?= $type ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="min_price" class="block text-sm font-medium text-gray-700">Prix Min (€)</label>
                        <input type="number" name="min_price" id="min_price" value="<?= htmlspecialchars($currentFilters['minPrice'] ?? '') ?>" class="mt-1 w-full border border-gray-300 rounded-md shadow-sm h-10 px-3">
                    </div>

                    <div>
                        <label for="max_price" class="block text-sm font-medium text-gray-700">Prix Max (€)</label>
                        <input type="number" name="max_price" id="max_price" value="<?= htmlspecialchars($currentFilters['maxPrice'] ?? '') ?>" class="mt-1 w-full border border-gray-300 rounded-md shadow-sm h-10 px-3">
                    </div>

                    <button type="submit" class="w-full h-10 px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition shadow-md">
                        Filtrer
                    </button>
                </div>

                <?php if (!empty($allEquipments)): ?>
                    <div class="pt-4 border-t border-gray-200">
                        <p class="font-medium text-gray-700 mb-2">Équipements requis (Logique AND)</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                            <?php
                            $selectedEquipments = (array)($currentFilters['equipmentIds'] ?? []);
                            foreach ($allEquipments as $equip):
                                $equipId = $equip->id ?? $equip['id'];
                                $isChecked = in_array($equipId, $selectedEquipments);
                                ?>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="equipments[]" value="<?= htmlspecialchars($equipId) ?>"
                                            <?= $isChecked ? 'checked' : '' ?>
                                           class="h-4 w-4 text-red-500 border-gray-300 rounded focus:ring-red-500">
                                    <span class="text-sm text-gray-700"><?= htmlspecialchars($equip->name ?? $equip['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div> </form>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const header = document.getElementById('filter-header');
                const content = document.getElementById('filter-content');
                const icon = document.getElementById('toggle-icon');

                // Détermine si des filtres sont appliqués
                const isFiltered = new URLSearchParams(window.location.search).toString().length > 0;

                // État initial : ouvert si un filtre est appliqué, sinon fermé
                if (!isFiltered) {
                    content.classList.add('hidden');
                    icon.classList.add('rotate-180'); // Fait tourner la flèche vers le haut (Fermé)
                }

                header.addEventListener('click', () => {
                    content.classList.toggle('hidden');
                    icon.classList.toggle('rotate-180'); // Alterne la rotation de la flèche
                });
            });
        </script>

        <?php if (!empty($rooms)): ?>
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Résultats (<?= count($rooms) ?>)</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($rooms as $room): ?>
                    <div class="bg-white rounded-xl shadow overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <?php $roomData = is_array($room) ? $room : (array)$room; ?>
                        <div class="h-48 w-full bg-gray-200 relative">
                            <?php if (!empty($roomData['media_path'])): ?>
                                <img src="/<?= htmlspecialchars($roomData['media_path']) ?>"
                                     alt="<?= htmlspecialchars($roomData['title'] ?? '') ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-400">🏠</div>
                            <?php endif; ?>
                        </div>

                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 truncate"><?= htmlspecialchars($roomData['city'] ?? '') ?>, <?= htmlspecialchars($roomData['country'] ?? '') ?></h3>
                            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($roomData['title'] ?? '') ?></p>
                            <div class="mt-2 text-md font-bold text-gray-900">
                                <?= htmlspecialchars($roomData['price_per_night'] ?? 0) ?>€ / nuit
                            </div>
                            <a href="/room/<?= $roomData['id'] ?>" class="text-red-500 text-sm mt-2 block hover:underline">Voir l'annonce</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Résultats (0)</h2>
            <div class="text-center py-20 bg-white rounded-xl shadow-md border border-gray-100">
                <p class="text-lg text-gray-600">Aucun logement ne correspond à vos critères de recherche.</p>
            </div>
        <?php endif; ?>
    </div>
</div>