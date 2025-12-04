<?php require dirname(__FILE__) . '/../_templates/_navbar.html.php'; ?>

<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Réservations sur mes biens</h1>

        <?php if (empty($reservations)): ?>
            <div class="bg-white p-10 rounded-xl shadow text-center">
                <p class="text-gray-500 text-lg">Aucune réservation reçue pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="p-4 font-semibold text-gray-600">Logement</th>
                        <th class="p-4 font-semibold text-gray-600">Voyageur</th>
                        <th class="p-4 font-semibold text-gray-600">Dates</th>
                        <th class="p-4 font-semibold text-gray-600">Durée</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <?php foreach ($reservations as $resa): ?>
                        <?php
                        $start = new DateTime($resa['start_date']);
                        $end = new DateTime($resa['end_date']);
                        $diff = $start->diff($end);
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($resa['media_path'])): ?>
                                        <img src="/<?= htmlspecialchars($resa['media_path']) ?>" class="w-12 h-12 rounded object-cover">
                                    <?php endif; ?>
                                    <span class="font-medium text-gray-800"><?= htmlspecialchars($resa['room_title']) ?></span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-medium"><?= htmlspecialchars($resa['guest_firstname'] . ' ' . $resa['guest_lastname']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($resa['guest_email']) ?></div>
                            </td>
                            <td class="p-4 text-sm">
                                <div>Du <span class="font-semibold"><?= $start->format('d/m/Y') ?></span></div>
                                <div>Au <span class="font-semibold"><?= $end->format('d/m/Y') ?></span></div>
                            </td>
                            <td class="p-4 text-sm text-gray-600">
                                <?= $diff->days ?> nuits
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>