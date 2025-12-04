<?php
// On s'assure que $room est défini (votre contrôleur le passera)
$title_page = "Modifier l'annonce";

// Initialisation de $errors si la vue n'en reçoit pas (pour l'affichage conditionnel)
$errors = $errors ?? [];
// Note: Dans l'édition, on utilise directement les valeurs de $room pour le remplissage
?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Modifier l'annonce</h1>

        <?php if (isset($errors['general'])): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600"><?= htmlspecialchars($errors['general']) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="/room/edit" enctype="multipart/form-data" class="space-y-6">
            <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
            <input type="hidden" name="id" value="<?= $room->id ?>">


            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($room->title) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['title']) ? 'border-red-500' : '' ?>" required maxlength="255">
                <?php if (isset($errors['title'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($room->country) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['country']) ? 'border-red-500' : '' ?>" required maxlength="100">
                    <?php if (isset($errors['country'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['country']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($room->city) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['city']) ? 'border-red-500' : '' ?>" required maxlength="100">
                    <?php if (isset($errors['city'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['city']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price_per_night" class="block text-sm font-medium text-gray-700 mb-2">Prix / nuit (€)</label>
                    <input type="number" id="price_per_night" name="price_per_night" value="<?= htmlspecialchars($room->price_per_night) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['price_per_night']) ? 'border-red-500' : '' ?>" required min="1" step="1">
                    <?php if (isset($errors['price_per_night'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['price_per_night']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="number_of_bed" class="block text-sm font-medium text-gray-700 mb-2">Lits</label>
                    <input type="number" id="number_of_bed" name="number_of_bed" value="<?= htmlspecialchars($room->number_of_bed) ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['number_of_bed']) ? 'border-red-500' : '' ?>" required min="1">
                    <?php if (isset($errors['number_of_bed'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['number_of_bed']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <?php $currentType = $room->type_of_room ?? ''; ?>
                <label for="type_of_room" class="block text-sm font-medium text-gray-700 mb-2">
                    Type de chambre <span class="text-red-500">*</span>
                </label>

                <select id="type_of_room" name="type_of_room"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2
                   focus:ring-blue-500 focus:border-transparent <?= isset($errors['type_of_room']) ? 'border-red-500' : '' ?>"
                        required>
                    <option value="">Sélectionnez un type de logement </option>

                    <option value="appartement" <?= empty($old['type_of_room']) || ($old['type_of_room'] === 'appartement') ? 'selected' : '' ?>>
                        Appartement
                    </option>
                    <option value="maison" <?= ($old['type_of_room'] ?? '') === 'maison' ? 'selected' : '' ?>>Maison</option>
                    <option value="studio" <?= ($old['type_of_room'] ?? '') === 'studio' ? 'selected' : '' ?>>Studio</option>
                    <option value="villa" <?= ($old['type_of_room'] ?? '') === 'villa' ? 'selected' : '' ?>>Villa</option>
                    <option value="chalet" <?= ($old['type_of_room'] ?? '') === 'chalet' ? 'selected' : '' ?>>Chalet</option>
                    <option value="bungalow" <?= ($old['type_of_room'] ?? '') === 'bungalow' ? 'selected' : '' ?>>Bungalow</option>
                    <option value="loft" <?= ($old['type_of_room'] ?? '') === 'loft' ? 'selected' : '' ?>>Loft</option>
                    <option value="duplex" <?= ($old['type_of_room'] ?? '') === 'duplex' ? 'selected' : '' ?>>Duplex</option>
                    <option value="tiny_house" <?= ($old['type_of_room'] ?? '') === 'tiny_house' ? 'selected' : '' ?>>Tiny House</option>
                    <option value="mobil_home" <?= ($old['type_of_room'] ?? '') === 'mobil_home' ? 'selected' : '' ?>>Mobil-home</option>
                    <option value="gite" <?= ($old['type_of_room'] ?? '') === 'gite' ? 'selected' : '' ?>>Gîte</option>
                    <option value="maison_hotes" <?= ($old['type_of_room'] ?? '') === 'maison_hotes' ? 'selected' : '' ?>>Maison d’hôtes</option>
                    <option value="chambre_privee" <?= ($old['type_of_room'] ?? '') === 'chambre_privee' ? 'selected' : '' ?>>Chambre privée</option>
                    <option value="chambre_partagee" <?= ($old['type_of_room'] ?? '') === 'chambre_partagee' ? 'selected' : '' ?>>Chambre partagée</option>
                    <option value="penthouse" <?= ($old['type_of_room'] ?? '') === 'penthouse' ? 'selected' : '' ?>>Penthouse</option>
                </select>

                <?php if (isset($errors['type_of_room'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['type_of_room']) ?></p>
                <?php endif; ?>
            </div>


            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($room->description) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Image de couverture</label>

                <?php if ($room->media_path): ?>
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 mb-1">Actuelle :</p>
                        <img src="/<?= htmlspecialchars($room->media_path) ?>" alt="Image actuelle" class="h-32 rounded object-cover border">
                    </div>
                <?php endif; ?>

                <label for="media" class="block w-full px-4 py-2 border border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 text-center">
                    <span class="text-gray-500 text-sm">Cliquez pour changer l'image (optionnel)</span>
                    <input type="file" id="media" name="media" accept="image/*" class="hidden">
                </label>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="w-full px-6 py-3 bg-[#FF5A5F] hover:bg-[#E14C50] text-white rounded-2xl font-bold transition">
                    Enregistrer les modifications
                </button>
                <a href="/mesAnnonces" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-100 transition flex items-center">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>