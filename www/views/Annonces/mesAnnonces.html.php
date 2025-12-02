<div class="min-h-screen p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Mes Annonces</h1>
            <div class="space-x-4">
                <a href="/room/create"
                    class="bg-[#FF5A5F] text-white px-6 py-2 rounded-full hover:bg-[#E14C50] transition shadow-md">
                    Ajouter un bien
                </a>
                <a href="/"
                   class="bg-gray-100 text-gray-800 px-4 py-2 rounded-full hover:bg-gray-200 transition">
                    Accueil
                </a>
            </div>

        </div>

        <?php if (!empty($rooms)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rooms as $room): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">

                        <div class="h-48 w-full bg-gray-200 relative">
                            <?php
                            // Supporte à la fois le format Array ($room['...']) et Objet ($room->...)
                            $mediaPath = is_array($room) ? ($room['media_path'] ?? null) : ($room->media_path ?? null);
                            $title = is_array($room) ? $room['title'] : $room->title;
                            $city = is_array($room) ? $room['city'] : $room->city;
                            $country = is_array($room) ? $room['country'] : $room->country;
                            $price = is_array($room) ? $room['price_per_night'] : $room->price_per_night;
                            $description = is_array($room) ? $room['description'] : $room->description;
                            ?>

                            <?php if ($mediaPath): ?>
                                <img src="/<?= htmlspecialchars($mediaPath) ?>"
                                     alt="<?= htmlspecialchars($title) ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h2 class="text-xl font-bold text-gray-800 truncate pr-2"><?= htmlspecialchars($title) ?></h2>
                                <span class="font-bold text-lg text-gray-900"><?= htmlspecialchars($price) ?>€ / nuit</span>
                            </div>

                            <p class="text-gray-500 text-sm mb-3">
                                <?= htmlspecialchars($city) ?>, <?= htmlspecialchars($country) ?>
                            </p>

                            <p class="text-gray-600 text-sm line-clamp-3">
                                <?= htmlspecialchars($description) ?>
                            </p>

                            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                <span class="text-xs text-gray-400">
                                    <?php  $dateActuelle = strtotime(date('Y-m-d')); ?>
                                    <?php $dateCreation = is_array($room) ? strtotime($room['created_at']) : strtotime($room->created_at); ?>
                                    <?php $diffJours = ceil(($dateActuelle - $dateCreation) / (60 * 60 * 24)); ?>
                                    <?php if ($diffJours > 0): ?>
                                    Annonce créée il y a <?= $diffJours ?> jour<?= $diffJours > 1 ? 's' : '' ?>
                                    <?php else: ?>
                                    Annonce créée aujourd'hui
                                    <?php endif; ?>
                                </span>
                                <?php $id = is_array($room) ? $room['id'] : $room->id; ?>
                                <a href="/room/edit?id=<?= $id ?>" class="text-[#FF5A5F] text-sm font-semibold hover:underline">Modifier</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <div class="inline-block p-6 rounded-full bg-gray-100 mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900">Aucun bien à afficher</h3>
                <p class="text-gray-500 mt-2 mb-6">Commencez par ajouter votre premier logement.</p>
                <a href="/room/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#FF5A5F] hover:bg-[#E14C50]">
                    Créer une annonce
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>