<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border border-gray-100">

        <h1 class="text-3xl font-extrabold text-gray-900 text-center">
            Réserver : <?= htmlspecialchars($room->title) ?>
        </h1>
        <p class="text-center text-gray-600">
            <?= htmlspecialchars($room->city) ?>, <?= htmlspecialchars($room->country) ?>
            &bull; <?= htmlspecialchars($room->price_per_night) ?>€ / nuit
        </p>

        <?php
        // Récupérer le message d'erreur flash et le stocker (le consommant de la session)
        $sessionError = \JulienLinard\Core\Session\Session::get('error');
        ?>

        <?php if ($sessionError): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <?= htmlspecialchars($sessionError) ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="/reservation/create" method="POST">
            <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
            <input type="hidden" name="room_id" value="<?= htmlspecialchars($room->id) ?>">

            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Date d'arrivée</label>
                    <input id="start_date" name="start_date" type="date" required
                           value="<?= htmlspecialchars($old['start_date'] ?? '') ?>"
                           min="<?= date('Y-m-d') ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#FF5A5F] focus:border-[#FF5A5F] <?= isset($errors['start_date']) || isset($errors['date']) ? 'border-red-500' : '' ?>">
                    <?php if (isset($errors['start_date'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($errors['start_date']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Date de départ</label>
                    <input id="end_date" name="end_date" type="date" required
                           value="<?= htmlspecialchars($old['end_date'] ?? '') ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#FF5A5F] focus:border-[#FF5A5F] <?= isset($errors['end_date']) || isset($errors['date']) ? 'border-red-500' : '' ?>">
                    <?php if (isset($errors['end_date'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($errors['end_date']) ?></p>
                    <?php endif; ?>
                    <?php if (isset($errors['date'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars($errors['date']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label for="comment" class="block text-sm font-medium text-gray-700">Commentaire (Optionnel)</label>
                <textarea id="comment" name="comment" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#FF5A5F] focus:border-[#FF5A5F]"><?= htmlspecialchars($old['comment'] ?? '') ?></textarea>
            </div>

            <div class="text-right text-lg font-bold">

                <div class="text-right text-lg font-bold">
                    Prix pour <span id="days">0</span> jour(s) : <span id="totalPrice">0</span>€
                </div>

                <script>
                    const startDateInput = document.getElementById('start_date');
                    const endDateInput = document.getElementById('end_date');
                    const daysSpan = document.getElementById('days');
                    const totalPriceSpan = document.getElementById('totalPrice');
                    const pricePerNight = <?= json_encode($room->price_per_night) ?>;

                    function updatePrice() {
                        const startDate = new Date(startDateInput.value);
                        const endDate = new Date(endDateInput.value);

                        if (startDate && endDate && endDate > startDate) {
                            const diffTime = endDate - startDate; // différence en ms
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // convertir en jours
                            daysSpan.textContent = diffDays;
                            totalPriceSpan.textContent = diffDays * pricePerNight;
                        } else {
                            daysSpan.textContent = 0;
                            totalPriceSpan.textContent = 0;
                        }
                    }

                    // Mettre à jour à chaque changement des inputs
                    startDateInput.addEventListener('change', updatePrice);
                    endDateInput.addEventListener('change', updatePrice);
                </script>


            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[#FF5A5F] hover:bg-[#E14C50] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF5A5F]">
                    Confirmer la réservation
                </button>
            </div>
        </form>
    </div>
</div>

<?php require dirname(__FILE__) . '/../_templates/_footer.html.php'; ?>