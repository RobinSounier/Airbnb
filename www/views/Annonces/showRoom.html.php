<?php
// On inclut la navbar
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