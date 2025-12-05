<?php
// --- Début du CONTENU DE LA PAGE DE GESTION DES RÉSERVATIONS ---

// Assurez-vous que $reservations est toujours un tableau (même vide)
$reservations = $reservations ?? [];
?>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">

        <a href="/" class="mb-6 inline-flex items-center text-gray-600 hover:text-[#FF5A5F] transition text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Retour à l'accueil
        </a>

        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">
            Réservations Reçues sur Mes Biens
        </h1>

        <?php if (\JulienLinard\Core\Session\Session::has('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?= htmlspecialchars(\JulienLinard\Core\Session\Session::get('success')) ?>
            </div>
        <?php elseif (\JulienLinard\Core\Session\Session::has('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?= htmlspecialchars(\JulienLinard\Core\Session\Session::get('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($reservations)): ?>
            <div class="space-y-6">
                <?php foreach ($reservations as $reservation): ?>
                    <?php
                    // --- Logique de calcul ---
                    $startDate = new \DateTime($reservation['start_date']);
                    $endDate = new \DateTime($reservation['end_date']);
                    $days = $startDate->diff($endDate)->days;

                    $pricePerNight = $reservation['price_per_night'] ?? 0;
                    $totalPrice = $days * $pricePerNight;

                    $status = $reservation['status'] ?? 'validee';
                    $isUpcoming = ($endDate > new \DateTime());

                    // Détermination du statut pour l'affichage
                    $statusText = match($status) {
                        'annulee' => 'Annulée',
                        default => $isUpcoming ? 'À Venir' : 'Terminée'
                    };
                    $statusColor = match($status) {
                        'annulee' => 'bg-red-500',
                        default => $isUpcoming ? 'bg-green-500' : 'bg-gray-400'
                    };
                    if (!$isUpcoming) {
                        $statusText = 'Terminée';
                        $statusColor = 'bg-gray-400';
                    }

                    $hasComment = !empty($reservation['comment']);
                    $guestName = htmlspecialchars(($reservation['guest_firstname'] ?? '') . ' ' . ($reservation['guest_lastname'] ?? ''));
                    ?>

                    <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">

                        <div class="md:w-1/3 h-48 md:h-auto flex-shrink-0 relative">
                            <?php if ($reservation['media_path']): ?>
                                <img class="w-full h-full object-cover"
                                     src="/<?= htmlspecialchars($reservation['media_path']) ?>"
                                     alt="<?= htmlspecialchars($reservation['room_title'] ?? 'Logement') ?>">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">Image manquante</div>
                            <?php endif; ?>

                            <span class="absolute top-3 left-3 px-3 py-1 text-xs font-semibold text-white rounded-full <?= $statusColor ?>">
                                <?= $statusText ?>
                            </span>
                        </div>

                        <div class="p-6 flex flex-col justify-between md:w-2/3">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 mb-1"><?= htmlspecialchars($reservation['room_title'] ?? 'Titre non spécifié') ?></h2>
                                <p class="text-sm text-gray-500 mb-4">Réservé par : <span class="font-medium text-gray-700"><?= $guestName ?></span></p>

                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="font-medium text-gray-700">Arrivée</p>
                                        <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($startDate->format('d M Y')) ?></p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Départ</p>
                                        <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($endDate->format('d M Y')) ?></p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="font-medium text-gray-700">Durée totale</p>
                                        <p class="text-lg font-semibold text-gray-900"><?= $days ?> nuit<?= $days > 1 ? 's' : '' ?></p>
                                    </div>
                                </div>

                                <?php if ($hasComment): ?>
                                    <div class="mt-4 pt-3 border-t border-gray-100">
                                        <p class="font-medium text-gray-700 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M7 8a1 1 0 011 1v2a1 1 0 11-2 0V9a1 1 0 011-1zM10 8a1 1 0 011 1v2a1 1 0 11-2 0V9a1 1 0 011-1zM13 8a1 1 0 011 1v2a1 1 0 11-2 0V9a1 1 0 011-1z"></path><path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-6l-4 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2z" clip-rule="evenodd"></path></svg>
                                            Commentaire de l'invité
                                        </p>
                                        <p class="text-sm italic text-gray-600 pl-5 border-l-2 border-gray-200">
                                            "<?= nl2br(htmlspecialchars($reservation['comment'])) ?>"
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="text-lg font-extrabold text-gray-900">
                                    Total de la réservation: <span class="text-[#FF5A5F]"><?= number_format($totalPrice, 0, ',', ' ') ?>€</span>
                                </div>

                                <span class="text-sm text-gray-500">Créée le <?= (new \DateTime($reservation['created_at']))->format('d/m/Y') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-xl shadow-md border border-gray-100">
                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">Aucune réservation reçue</h3>
                <p class="mt-1 text-sm text-gray-500">Partagez vos annonces pour recevoir vos premières réservations.</p>
                <a href="/mesAnnonces" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#FF5A5F] hover:bg-[#E14C50]">
                    Voir mes annonces
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>