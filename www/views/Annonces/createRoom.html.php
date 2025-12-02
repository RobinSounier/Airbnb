<?php
$errors = $errors ?? [];
$old = $old ?? [
    'title' => '',
    'country' => '',
    'city' => '',
    'price_per_night' => '',
    'description' => '',
    'number_of_bed' => ''
];
?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Ajouter une Chambre</h1>

        <?php if (isset($errors['general'])): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600"><?= htmlspecialchars($errors['general']) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="/room/create" enctype="multipart/form-data" class="space-y-6">
            <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['title']) ? 'border-red-500' : '' ?>" required maxlength="255">
                <?php if (isset($errors['title'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Country -->
            <div>
                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Pays <span class="text-red-500">*</span></label>
                <input type="text" id="country" name="country" value="<?= htmlspecialchars($old['country'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['country']) ? 'border-red-500' : '' ?>" required maxlength="100">
                <?php if (isset($errors['country'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['country']) ?></p>
                <?php endif; ?>
            </div>

            <!-- City -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Ville <span class="text-red-500">*</span></label>
                <input type="text" id="city" name="city" value="<?= htmlspecialchars($old['city'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['city']) ? 'border-red-500' : '' ?>" required maxlength="100">
                <?php if (isset($errors['city'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['city']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Price per night -->
            <div>
                <label for="price_per_night" class="block text-sm font-medium text-gray-700 mb-2">Prix par nuit (€) <span class="text-red-500">*</span></label>
                <input type="number" id="price_per_night" name="price_per_night" value="<?= htmlspecialchars($old['price_per_night'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['price_per_night']) ? 'border-red-500' : '' ?>" required min="1" step="0.01">
                <?php if (isset($errors['price_per_night'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['price_per_night']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Number of bed -->
            <div>
                <label for="number_of_bed" class="block text-sm font-medium text-gray-700 mb-2">Nombre de lits <span class="text-red-500">*</span></label>
                <input type="number" id="number_of_bed" name="number_of_bed" value="<?= htmlspecialchars($old['number_of_bed'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?= isset($errors['number_of_bed']) ? 'border-red-500' : '' ?>" required min="1" max="20">
                <?php if (isset($errors['number_of_bed'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($errors['number_of_bed']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <!-- Media Upload -->
            <div>
                <label for="media" class="block text-sm font-medium text-gray-700 mb-2">Images (optionnel)</label>
                <div class="space-y-4">
                    <div class="flex items-center justify-center w-full">
                        <label for="media_path" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">Cliquez pour uploader</span> ou glissez-déposez
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF, WebP jusqu'à 10MB (max 10 fichiers)</p>
                            </div>
                            <input type="file" id="media_path" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="previewImages(this)">
                        </label>
                    </div>
                    <div id="imagePreview" class="grid grid-cols-2 md:grid-cols-4 gap-4 hidden"></div>
                </div>
            </div>

            <script>
                let fileIndex = 0;
                const fileMap = new Map();
                function previewImages(input) {
                    const preview = document.getElementById('imagePreview');
                    if (input.files && input.files.length > 0) {
                        preview.classList.remove('hidden');
                        fileMap.clear();
                        preview.innerHTML = '';
                        fileIndex = 0;
                        Array.from(input.files).forEach(file => {
                            if (file.type.startsWith('image/')) {
                                const currentIndex = fileIndex++;
                                fileMap.set(currentIndex, file);
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const div = document.createElement('div');
                                    div.className = 'relative border border-gray-200 rounded-lg overflow-hidden';
                                    div.setAttribute('data-index', currentIndex);
                                    div.innerHTML = `
                                    <img src="${e.target.result}" alt="${file.name}" class="w-full h-32 object-cover">
                                    <button type="button" onclick="removeImage(${currentIndex})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                                    <p class="text-xs text-gray-600 p-1 truncate" title="${file.name}">${file.name}</p>
                                `;
                                    preview.appendChild(div);
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    } else {
                        preview.classList.add('hidden');
                        fileMap.clear();
                    }
                }
                function removeImage(index) {
                    const input = document.getElementById('media');
                    fileMap.delete(index);
                    const dt = new DataTransfer();
                    fileMap.forEach(file => dt.items.add(file));
                    input.files = dt.files;
                    previewImages(input);
                }
            </script>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="w-full px-6 py-3 bg-[#FF5A5F] hover:bg-[#E14C50] text-white rounded-2xl
                               font-semibold shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5"">Créer
                </button>
                <a href="/mesAnnonces" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-150">Annuler</a>
            </div>
        </form>
    </div>
</div>
