<?php
// On inclut la navbar
require dirname(__FILE__) . '/../_templates/_navbar.html.php';
?>

    <div class="min-h-screen bg-gray-50 pb-12">
        <div class="bg-white border-b border-gray-200 py-6 mb-8 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-extrabold text-gray-900">Explorez des logements uniques</h1>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (!empty($rooms)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    <?php foreach ($rooms as $room): ?>

                        <div class="group block w-full rounded-xl cursor-pointer hover:shadow-xl transition-shadow duration-300 bg-white overflow-hidden border border-gray-100">
                            <?php
                            // Gestion compatibilité Objet/Tableau
                            $mediaPath = is_array($room) ? ($room['media_path'] ?? null) : ($room->media_path ?? null);
                            $title = is_array($room) ? $room['title'] : $room->title;
                            $city = is_array($room) ? $room['city'] : $room->city;
                            $country = is_array($room) ? $room['country'] : $room->country;
                            $price = is_array($room) ? $room['price_per_night'] : $room->price_per_night;
                            $number_of_bed = is_array($room) ? $room['number_of_bed'] : $room->number_of_bed;
                            $description = is_array($room) ? $room['description'] : $room->description;
                            $id = is_array($room) ? $room['id'] : $room->id;
                            $roomType = is_array($room) ? ($room['type_of_room'] ?? 'Non spécifié') : ($room->type_of_room ?? 'Non spécifié');
                            ?>

                            <a href="/room/<?= htmlspecialchars($id) ?>" class="block" title="<?= htmlspecialchars($title) ?>">

                                <div class="aspect-w-16 aspect-h-9 relative h-64 bg-gray-200">
                                    <?php if ($mediaPath): ?>
                                        <img src="/<?= htmlspecialchars($mediaPath) ?>" alt="<?= htmlspecialchars($title) ?>" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                    <?php else: ?>
                                        <div class="flex items-center justify-center w-full h-full bg-gray-100 text-gray-400">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>

                                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-bold shadow-sm text-gray-900">
                                        <?= htmlspecialchars($price) ?> € <span class="font-normal text-xs text-gray-500">/ nuit</span>
                                    </div>
                                    <div class="absolute top-12 right-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-bold shadow-sm text-gray-900">
                                        <?= htmlspecialchars($number_of_bed) ?> <span class="font-normal text-xs text-gray-500"> lits</span>
                                    </div>
                                </div>

                                <div class="p-4 pb-2">
                                    <div class="flex justify-between items-start mb-1">
                                        <h3 class="font-bold text-gray-900 truncate"><?= htmlspecialchars($title) ?></h3>
                                    </div>

                                    <p class="text-xs font-semibold text-blue-600 mb-2">
                                        <?= htmlspecialchars($roomType) ?>
                                    </p>

                                    <p></p>

                                    <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($city) ?>, <?= htmlspecialchars($country) ?></p>
                                </div>
                            </a>
                            <div class="px-4 pb-4 pt-0">
                                <a href="/reservation/create?room_id=<?= $id ?>" class="block w-full py-2.5 text-center text-white font-medium bg-[#FF5A5F] hover:bg-[#E14C50] rounded-lg transition-colors shadow-md hover:shadow-lg">
                                    Réserver
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-24 bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-6">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Aucun logement disponible pour le moment</h3>
                    <p class="mt-2 text-gray-500">Revenez plus tard pour découvrir de nouvelles offres.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php require dirname(__FILE__) . '/../_templates/_footer.html.php'; ?>