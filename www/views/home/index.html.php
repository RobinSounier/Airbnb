<?php
// On inclut la navbar
require dirname(__FILE__) . '/../_templates/_navbar.html.php';

// Assurez-vous que $rooms et $allEquipments sont définis, même à vide
$rooms = $rooms ?? [];
$allEquipments = $allEquipments ?? [];
$currentFilters = $currentFilters ?? [];

// Définition des valeurs initiales et maximales pour le curseur
$initialMinPrice = $currentFilters['minPrice'] ?? 0;
$initialMaxPrice = $currentFilters['maxPrice'] ?? 2000;
$maxRangeValue = 2000;
?>

<style>
    .range-slider {
        pointer-events: none;
    }


    .range-slider::-webkit-slider-thumb {
        pointer-events: all;
        -webkit-appearance: none;
        height: 16px;
        width: 16px;
        border-radius: 50%;
        background: #ef4444;
        cursor: pointer;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
        transition: background 0.15s ease-in-out;
        margin-top: -8px;
    }

    .range-slider::-moz-range-thumb {
        pointer-events: all;
        border: none;
        height: 16px;
        width: 16px;
        border-radius: 50%;
        background: #ef4444;
        cursor: pointer;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
        transition: background 0.15s ease-in-out;
    }
</style>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto lg:grid lg:grid-cols-4 lg:gap-8">

        <div class="lg:col-span-1">
            <form method="GET" action="/" class="bg-white shadow-md rounded-xl p-6 mb-8 border border-gray-100 sticky top-4">

                <div class="flex justify-between items-center cursor-pointer mb-2" id="filter-header">
                    <h2 class="text-xl font-bold text-gray-800">Filtrer les annonces</h2>
                    <button type="button" class="lg:hidden text-gray-500 hover:text-gray-700 transition" id="filter-toggle-btn">
                        <svg id="toggle-icon" class="w-6 h-6 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>

                <div id="filter-content" class="space-y-6 hidden lg:block">

                    <div class="space-y-4">
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
                                $types = ['Appartement', 'Maison', 'Studio', 'Villa', 'Chalet', 'Loft', 'duplex', 'tiny House', 'mobil Home', 'gite', 'maison Hotes', 'chambre Privee', 'chambre Partagée', 'penthouse'];
                                foreach ($types as $type):
                                    ?>
                                    <option value="<?= strtolower($type) ?>" <?= (($currentFilters['roomType'] ?? '') === strtolower($type)) ? 'selected' : '' ?>>
                                        <?= $type ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="block text-sm font-medium text-gray-700 mb-4">Prix (€) : <span id="price-range-display" class="font-bold text-red-600"></span></p>

                        <div class="relative pt-1">
                            <input type="hidden" name="min_price" id="min_price_hidden" value="<?= htmlspecialchars($initialMinPrice) ?>">
                            <input type="hidden" name="max_price" id="max_price_hidden" value="<?= htmlspecialchars($initialMaxPrice) ?>">

                            <input type="range" min="0" max="<?= $maxRangeValue ?>" value="<?= htmlspecialchars($initialMinPrice) ?>" step="10"
                                   id="min_price_slider" class="absolute w-full h-2 bg-transparent appearance-none cursor-pointer range-slider z-30">

                            <input type="range" min="0" max="<?= $maxRangeValue ?>" value="<?= htmlspecialchars($initialMaxPrice) ?>" step="10"
                                   id="max_price_slider" class="absolute w-full h-2 bg-transparent appearance-none cursor-pointer range-slider z-30">

                            <div class="relative w-full h-1 bg-gray-200 rounded-full">
                                <div id="price-range-track" class="absolute h-1 bg-red-500 rounded-full"></div>
                            </div>
                        </div>

                        <div class="flex justify-between text-xs text-gray-500 mt-5">
                            <span>0 €</span>
                            <span><?= $maxRangeValue ?> €</span>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-200 mt-4">
                        <button type="submit" class="w-full h-10 px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition shadow-md">
                            Filtrer
                        </button>
                    </div>

                    <?php if (!empty($allEquipments)): ?>
                        <div class="pt-4 border-t border-gray-200 mt-6">
                            <p class="font-medium text-gray-700 mb-2">Équipements requis</p>
                            <div class="grid grid-cols-2 gap-3">
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

                </div>
            </form>
        </div>

        <div class="lg:col-span-3">
            <?php if (!empty($rooms)): ?>
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Résultats (<?= count($rooms) ?>)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
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
</div>

<script>
    const MAX_RANGE_VALUE = 2000;

    document.addEventListener('DOMContentLoaded', () => {
        // --- Logique du Double Curseur ---
        const minPriceSlider = document.getElementById('min_price_slider');
        const maxPriceSlider = document.getElementById('max_price_slider');
        const minPriceHidden = document.getElementById('min_price_hidden');
        const maxPriceHidden = document.getElementById('max_price_hidden');
        const rangeDisplay = document.getElementById('price-range-display');
        const rangeTrack = document.getElementById('price-range-track');

        function updateRange() {
            let minVal = parseInt(minPriceSlider.value);
            let maxVal = parseInt(maxPriceSlider.value);

            // S'assurer que le minimum ne dépasse jamais le maximum
            if (minVal > maxVal) {
                // Si min devient plus grand que max, on les inverse temporairement pour l'affichage
                [minVal, maxVal] = [maxVal, minVal];
            }

            // Mise à jour des champs cachés pour l'envoi du formulaire
            minPriceHidden.value = minPriceSlider.value; // On garde les valeurs originales du curseur pour l'envoi
            maxPriceHidden.value = maxPriceSlider.value;

            // Mise à jour de l'affichage de la plage (utilise les valeurs inversées si nécessaire pour l'affichage propre)
            rangeDisplay.textContent = `${minVal.toLocaleString()} € - ${maxVal.toLocaleString()} €`;

            // Mise à jour de la piste colorée (visuel)
            const minPercent = (minVal / MAX_RANGE_VALUE) * 100;
            const maxPercent = (maxVal / MAX_RANGE_VALUE) * 100;

            rangeTrack.style.left = `${minPercent}%`;
            rangeTrack.style.width = `${maxPercent - minPercent}%`;
        }

        // Initialiser l'affichage et la piste au chargement avec les valeurs PHP
        updateRange();

        // Événements d'écoute
        minPriceSlider.addEventListener('input', updateRange);
        maxPriceSlider.addEventListener('input', updateRange);


        // --- Logique de la Bascule (Toggle) ---
        const header = document.getElementById('filter-header');
        const content = document.getElementById('filter-content');
        const icon = document.getElementById('toggle-icon');

        const isFiltered = new URLSearchParams(window.location.search).toString().length > 0;

        if (window.innerWidth < 1024) {

            if (!isFiltered) {
                content.classList.add('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.remove('hidden');
                icon.classList.remove('rotate-180');
            }

            header.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    content.classList.toggle('hidden');
                    icon.classList.toggle('rotate-180');
                }
            });
        }
    });
</script>