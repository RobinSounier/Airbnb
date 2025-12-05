<?php
// On inclut la navbar
global $name;
require dirname(__FILE__) . '/../_templates/_navbar.html.php';
?>
<div class="max-w-4xl mx-auto py-10">
    <a href="/" class="text-gray-600 hover:text-[#FF5A5F] mb-4 inline-block">&larr; Retour à l'accueil</a>

    <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($room->title) ?></h1>

    <?php if ($room->media_path): ?>
        <img src="/<?= htmlspecialchars($room->media_path) ?>"
             alt="<?= htmlspecialchars($room->title) ?>"
             class="w-full h-96 object-cover rounded-lg shadow-lg mb-6">
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <p class="text-xl font-semibold text-gray-800 mb-4">
            <?= htmlspecialchars($room->price_per_night) ?>€ <span class="text-gray-500 font-normal">par nuit</span>
        </p>

        <h2 class="text-2xl font-semibold mb-3">Description</h2>
        <p class="text-gray-700 mb-6"><?= nl2br(htmlspecialchars($room->description)) ?></p>

        <h2 class="2xl font-semibold mb-3">Détails</h2>
        <ul class="space-y-2 text-gray-600">
            <li>
                <span class="font-medium">Type :</span>
                <?= htmlspecialchars($room->type ?? 'Logement') ?>
            </li>

            <li><span class="font-medium">Lieu :</span> <?= htmlspecialchars($room->city) ?>, <?= htmlspecialchars($room->country) ?></li>
            <li><span class="font-medium">Lits :</span> <?= htmlspecialchars($room->number_of_bed) ?></li>
        </ul>

        <h2 class="text-2xl font-semibold mb-4">Ce que propose ce logement</h2>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <?php foreach ($room->equipments as $equip): ?>
                <div class="flex items-center text-gray-600">
                    <span class="mr-3 text-lg">✨</span>
                    <span><?= htmlspecialchars($equip->name) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($ownerName) && !empty($ownerName)): ?>
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-2xl font-semibold mb-4">À propos de l'hôte</h3>

                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-8 w-8 text-[#FF5A5F] mt-1 mr-4 flex-shrink-0"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>

                    <div>
                        <p class="text-gray-700">
                            Cet hébergement est proposé par :
                        </p>
                        <p class="font-bold text-xl text-gray-900">
                            <?= htmlspecialchars($ownerName['first_name']) ?>
                            <?= htmlspecialchars($ownerName['last_name']) ?>
                        </p>
                    </div>
                </div>

            </div>
        <?php endif; ?>

        <?php
        // --- Définit la variable $id à partir de l'objet $room ---
        $id = $room->id;
        ?>

        <div class="mt-8">
            <a href="/reservation/create?room_id=<?= $id ?>">
                <button class="w-full bg-[#FF5A5F] text-white py-3 rounded-lg text-lg font-semibold hover:bg-[#E14C50] transition">
                    Réserver ce logement
                </button>
            </a>
        </div>
    </div>
</div>