<?php
// On inclut la navbar
global $name;
require dirname(__FILE__) . '/../_templates/_navbar.html.php';
?>

<div class="max-w-6xl mx-auto py-14">
    <a href="/" class="text-gray-500 hover:text-[#FF5A5F] inline-flex items-center gap-2 mb-8 transition font-medium">
        <span class="text-lg">←</span> Retour
    </a>

    <header class="mb-10">
        <h1 class="text-4xl font-black tracking-tight text-gray-900">
            <?= htmlspecialchars($room->title) ?>
        </h1>
    </header>

    <?php if ($room->media_path): ?>
        <div class="rounded-2xl overflow-hidden shadow-2xl mb-12 border border-gray-200">
            <img src="/<?= htmlspecialchars($room->media_path) ?>"
                 alt="<?= htmlspecialchars($room->title) ?>"
                 class="w-full h-[450px] object-cover">
        </div>
    <?php endif; ?>

    <section class="bg-white p-10 rounded-2xl shadow-xl border border-gray-100">

        <div class="mb-10 pb-6 border-b border-gray-200 flex items-end justify-between">
            <div>
                <p class="text-3xl font-extrabold text-gray-900">
                    <?= htmlspecialchars($room->price_per_night) ?>€
                    <span class="text-gray-500 text-lg font-medium">/ nuit</span>
                </p>
            </div>
        </div>

        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Présentation</h2>
            <p class="text-gray-700 leading-relaxed text-lg">
                <?= nl2br(htmlspecialchars($room->description)) ?>
            </p>
        </div>

        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Informations clés</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="p-5 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-gray-500 text-sm">Type</p>
                    <p class="font-semibold text-gray-900 text-lg">
                        <?= htmlspecialchars($room->type_of_room ?? 'Logement') ?>
                    </p>
                </div>

                <div class="p-5 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-gray-500 text-sm">Lieu</p>
                    <p class="font-semibold text-gray-900 text-lg">
                        <?= htmlspecialchars($room->city) ?>, <?= htmlspecialchars($room->country) ?>
                    </p>
                </div>

                <div class="p-5 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-gray-500 text-sm">Nombre de lits</p>
                    <p class="font-semibold text-gray-900 text-lg">
                        <?= htmlspecialchars($room->number_of_bed) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Équipements</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($room->equipments as $equip): ?>
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-white border border-gray-200 shadow-sm">
                        <div class="text-[#FF5A5F] text-xl">◆</div>
                        <p class="text-gray-700 font-medium">
                            <?= htmlspecialchars($equip->name) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (isset($hostName) && !empty($hostName)): ?>
            <div class="pt-10 border-t border-gray-300">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Hôte — Présentation</h3>

                <div class="flex items-center gap-5">
    <div class="h-16 w-16 rounded-full bg-[#FF5A5F] text-white flex items-center justify-center text-2xl font-bold shadow-lg">
        <?= strtoupper(substr($hostName['first_name'], 0, 1)) ?>
    </div>

    <div>
        <p class="text-gray-700 text-sm mb-1">Hébergement proposé par</p>
        <p class="text-2xl font-bold text-gray-900 leading-tight">
            <?= htmlspecialchars($hostName['first_name']) ?>
            <?= htmlspecialchars($hostName['last_name']) ?>
        </p>

    </div>
</div>
                <?php endif; ?>


                <?php $id = $room->id; ?>

                <div class="mt-12">
                    <a href="/reservation/create?room_id=<?= $id ?>">
                        <button class="w-full bg-[#FF5A5F] text-white py-4 rounded-xl text-xl font-semibold shadow-md hover:shadow-xl hover:bg-[#E14C50] transition-all duration-200">
                            Réserver maintenant
                        </button>
                    </a>
                </div>

    </section>
</div>
