<?php
// --- Page de gestion des réservations (NOUVEAU STYLE ULTRA MODERNE) ---
$reservations = $reservations ?? [];
?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-14 px-6">
    <div class="max-w-7xl mx-auto space-y-10">

        <a href="/" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition text-sm font-medium group">
            <svg class="w-4 h-4 mr-1 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Retour à l'accueil
        </a>

        <h1 class="text-4xl font-black text-gray-900 tracking-tight">Mes Réservations Reçues</h1>

        <?php if (\JulienLinard\Core\Session\Session::has('success')): ?>
            <div class="bg-green-50 border border-green-300 text-green-700 px-5 py-3 rounded-lg text-sm shadow-sm">
                <?= htmlspecialchars(\JulienLinard\Core\Session\Session::get('success')) ?>
            </div>
        <?php elseif (\JulienLinard\Core\Session\Session::has('error')): ?>
            <div class="bg-red-50 border border-red-300 text-red-700 px-5 py-3 rounded-lg text-sm shadow-sm">
                <?= htmlspecialchars(\JulienLinard\Core\Session\Session::get('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($reservations)): ?>
            <div class="space-y-8">
                <?php foreach ($reservations as $reservation): ?>
                    <?php
                    $startDate = new DateTime($reservation['start_date']);
                    $endDate = new DateTime($reservation['end_date']);
                    $days = $startDate->diff($endDate)->days;
                    $pricePerNight = $reservation['price_per_night'] ?? 0;
                    $totalPrice = $days * $pricePerNight;

                    $status = $reservation['status'] ?? 'validee';
                    $isUpcoming = ($endDate > new DateTime());

                    $statusText = match($status) {
                        'annulee' => 'Annulée',
                        default => $isUpcoming ? 'À Venir' : 'Terminée'
                    };

                    $statusColor = match($status) {
                        'annulee' => 'bg-red-600',
                        default => $isUpcoming ? 'bg-green-600' : 'bg-gray-500'
                    };

                    if (!$isUpcoming) {
                        $statusText = 'Terminée';
                        $statusColor = 'bg-gray-500';
                    }

                    $hasComment = !empty($reservation['comment']);
                    $guestName = htmlspecialchars(($reservation['guest_firstname'] ?? '') . ' ' . ($reservation['guest_lastname'] ?? ''));
                    ?>

                    <div class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition border border-gray-200 overflow-hidden">
                        <div class="flex flex-col md:flex-row">

                            <div class="md:w-1/3 h-56 md:h-auto relative overflow-hidden">
                                <?php if ($reservation['media_path']): ?>
                                    <img src="/<?= htmlspecialchars($reservation['media_path']) ?>"
                                         class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700"
                                         alt="<?= htmlspecialchars($reservation['room_title'] ?? 'Logement') ?>">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400 text-sm">Image manquante</div>
                                <?php endif; ?>

                                <span class="absolute top-4 left-4 px-3 py-1 text-xs font-semibold text-white rounded-full <?= $statusColor ?> shadow">
                                    <?= $statusText ?>
                                </span>
                            </div>

                            <div class="p-8 flex flex-col justify-between md:w-2/3 space-y-6">
                                <div class="space-y-4">
                                    <h2 class="text-2xl font-bold text-gray-900">
                                        <?= htmlspecialchars($reservation['room_title'] ?? 'Titre non spécifié') ?>
                                    </h2>

                                    <p class="text-sm text-gray-600">Réservé par
                                        <span class="font-semibold text-gray-800"><?= $guestName ?></span>
                                    </p>

                                    <div class="grid grid-cols-2 gap-6 text-sm">
                                        <div>
                                            <p class="font-medium text-gray-600">Arrivée</p>
                                            <p class="text-lg font-semibold text-gray-900"><?= $startDate->format('d M Y') ?></p>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-600">Départ</p>
                                            <p class="text-lg font-semibold text-gray-900"><?= $endDate->format('d M Y') ?></p>
                                        </div>
                                        <div class="col-span-2">
                                            <p class="font-medium text-gray-600">Durée totale</p>
                                            <p class="text-lg font-semibold text-gray-900"><?php echo $days ?> nuit<?= $days > 1 ? 's' : '' ?></p>
                                        </div>
                                    </div>

                                    <?php if ($hasComment): ?>
                                        <div class="pt-4 border-t border-gray-200">
                                            <p class="font-medium text-gray-700 flex items-center mb-2">
                                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M7 8a1 1 0 011 1v2a1 1 0 11-2 0V9a1 1 0 011-1zM10 8a1 1 0 011 1v2a1 1 0 11-2 0V9a1 1 0 011-1zM13 8a1 1 0 011 1v2a1 1 0 11-2 0V9a1 1 0 011-1z"></path><path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-6l-4 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2z" clip-rule="evenodd"></path></svg>
                                                Commentaire de l'invité
                                            </p>
                                            <p class="italic text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-200 text-sm">
                                                "<?= nl2br(htmlspecialchars($reservation['comment'])) ?>"
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="pt-5 border-t border-gray-100 flex items-center justify-between">
                                    <div class="text-xl font-black text-gray-900">
                                        Total : <span class="text-rose-600"><?= number_format($totalPrice, 0, ',', ' ') ?>€</span>
                                    </div>

                                    <span class="text-xs text-gray-500">Créée le <?= (new DateTime($reservation['created_at']))->format('d/m/Y') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="text-center py-24 bg-white rounded-2xl shadow-lg border border-gray-200">
                <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-xl font-bold text-gray-900">Aucune réservation pour le moment</h3>
                <p class="mt-2 text-gray-500">Partagez vos annonces pour attirer davantage d'invités.</p>
                <a href="/mesAnnonces" class="mt-6 inline-flex items-center px-6 py-3 rounded-lg text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 transition shadow">
                    Voir mes annonces
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>