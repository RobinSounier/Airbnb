<?php
// On inclut la navbar
global $name;
require dirname(__FILE__) . '/../_templates/_navbar.html.php';
?>

<div class="font-sans bg-white">

    <?php if ($room->media_path): ?>
        <div class="w-full mb-12 shadow-lg">
            <img src="/<?= htmlspecialchars($room->media_path) ?>"
                 alt="<?= htmlspecialchars($room->title) ?>"
                 class="w-full max-h-[700px] object-cover rounded-b-3xl">
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">

        <a href="/" class="text-gray-600 hover:text-[#FF5A5F] inline-flex items-center gap-2 mb-8 transition font-medium text-base">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Retour aux annonces
        </a>

        <header class="mb-10 pb-6 border-b border-gray-200">
            <h1 class="text-5xl font-extrabold tracking-tight text-gray-900">
                <?= htmlspecialchars($room->title) ?>
            </h1>
            <p class="text-xl text-gray-600 mt-2 font-light">
                <?= htmlspecialchars($room->city) ?>, <?= htmlspecialchars($room->country) ?>
            </p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-16">

            <div class="lg:col-span-2 space-y-12 border-r lg:pr-10 border-gray-100">

                <div class="flex items-center space-x-6 text-gray-700 text-lg border-b pb-6 border-gray-200">
                    <p>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($room->type_of_room ?? 'Logement') ?></span>
                    </p>
                    <span class="text-gray-400">|</span>
                    <p>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($room->number_of_bed) ?></span> Lits
                    </p>
                </div>

                <div class="border-b border-gray-200 pb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">À propos de ce logement</h2>
                    <p class="text-gray-700 leading-relaxed text-lg whitespace-pre-wrap">
                        <?= nl2br(htmlspecialchars($room->description)) ?>
                    </p>
                </div>

                <div class="border-b border-gray-200 pb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Ce que vous trouverez</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php foreach ($room->equipments as $equip): ?>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-gray-200 shadow-sm">
                                <div class="text-[#FF5A5F] text-xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-gray-700 font-medium text-base">
                                    <?= htmlspecialchars($equip->name) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (isset($hostName) && !empty($hostName)): ?>
                    <div class="pt-8 border-t border-gray-200">
                        <h3 class="text-3xl font-bold text-gray-900 mb-6">Rencontrez l'Hôte</h3>

                        <div class="flex items-center gap-5 p-6 border border-gray-200 rounded-xl">
                            <div class="h-16 w-16 rounded-full bg-[#FF5A5F] text-white flex items-center justify-center text-2xl font-bold shadow-md">
                                <?= strtoupper(substr($hostName['first_name'], 0, 1)) ?>
                            </div>

                            <div>
                                <p class="text-gray-500 text-sm mb-1">Hébergement proposé par</p>
                                <p class="text-2xl font-bold text-gray-900 leading-tight">
                                    <?= htmlspecialchars($hostName['first_name']) ?>
                                    <?= htmlspecialchars($hostName['last_name']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-1 mt-10 lg:mt-0">
                <section class="lg:sticky lg:top-8 p-6 rounded-2xl border border-gray-200 shadow-2xl bg-white">
                    <div class="mb-8">
                        <p class="text-4xl font-extrabold text-gray-900">
                            <?= htmlspecialchars($room->price_per_night) ?>€
                            <span class="text-gray-500 text-lg font-medium">/ nuit</span>
                        </p>
                    </div>

                    <div class="border border-gray-300 rounded-lg mb-6 p-4 text-center text-gray-500">
                        <p>Sélectionnez vos dates pour voir le prix total.</p>
                    </div>

                    <?php $id = $room->id; ?>

                    <a href="/reservation/create?room_id=<?= $id ?>">
                        <button class="w-full bg-[#FF5A5F] text-white py-4 rounded-lg text-xl font-semibold shadow-md hover:shadow-lg hover:bg-[#E14C50] transition-all duration-200">
                            Vérifier la disponibilité
                        </button>
                    </a>
                </section>
            </div>
        </div>
    </div>
</div>