<?php // Assurez-vous d'inclure votre navbar ici require dirname(__FILE__) . '/../_templates/_navbar.html.php'; ?>
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8"><h1 class="text-3xl font-bold text-gray-900">Réservations
                sur mes biens</h1>             <a href="/mesAnnonces"
                                                  class="text-[#FF5A5F] hover:text-[#E14C50] font-medium"> &larr; Retour
                à mes annonces </a></div> <?php if (empty($reservations)): ?>
            <div class="bg-white rounded-xl shadow-md p-12 text-center border border-gray-200"><p
                        class="text-gray-500 text-lg">Aucune réservation reçue pour le moment.</p>
            </div>         <?php else: ?>
            <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Logement & Image
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Voyageur (Invité)
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dates & Durée
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prix Total
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">                         <?php foreach ($reservations as $resa): ?><?php // Utilisation de DateTime pour manipuler les dates                             $start = new DateTime($resa['start_date']);                             $end = new DateTime($resa['end_date']);                             $duration = $start->diff($end)->days;                              // Calcul du prix total                             $totalPrice = $duration * $resa['price_per_night'];                              // Simulation d'un statut (à adapter si vous ajoutez un champ 'status' à votre BDD)                             $statusLabel = 'Nouvelle';                             $statusClass = 'bg-blue-100 text-blue-800';                              // Si la date de fin est passée, c'est terminé                             if ($end < new DateTime()) {                                 $statusLabel = 'Terminée';                                 $statusClass = 'bg-gray-100 text-gray-800';                             }                             ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">                                         <?php if (!empty($resa['media_path'])): ?>
                                            <img class="h-12 w-12 rounded-lg object-cover mr-3"
                                                 src="/<?= htmlspecialchars($resa['media_path']) ?>"
                                                 alt="<?= htmlspecialchars($resa['room_title']) ?>">                                         <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($resa['room_title']) ?></div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($resa['price_per_night']) ?>
                                                € / nuit
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">                                         <?= htmlspecialchars($resa['guest_firstname'] . ' ' . $resa['guest_lastname']) ?>                                     </div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($resa['guest_email']) ?></div>
                                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">                                             <?= $statusLabel ?>                                         </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"> Du <span
                                                class="font-semibold"><?= $start->format('d/m/Y') ?></span></div>
                                    <div class="text-sm text-gray-900"> Au <span
                                                class="font-semibold"><?= $end->format('d/m/Y') ?></span></div>
                                    <div class="text-xs text-gray-500 mt-1"><?= $duration ?> nuits</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-lg font-bold text-[#FF5A5F]">                                     <?= number_format($totalPrice, 2, ',', ' ') ?>
                                    €
                                </td>
                            </tr>                         <?php endforeach; ?>                         </tbody>
                    </table>
                </div>
            </div>         <?php endif; ?>     </div>
</div>