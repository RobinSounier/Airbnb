
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-8 bg-white p-10 rounded-xl shadow-xl border border-gray-100 text-center">

        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-6 mx-auto">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h1 class="text-4xl font-extrabold text-green-700">
            Réservation Confirmée !
        </h1>

        <p class="mt-4 text-gray-700 text-lg">
            <?= \JulienLinard\Core\Session\Session::get('success') ?>
        </p>

        <p class="text-gray-500">
            Votre réservation a été enregistrée avec succès. Vous pouvez retrouver les détails dans votre espace personnel.
        </p>

        <div class="mt-8 flex justify-center space-x-4">
            <a href="/"
               class="py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition shadow-md">
                Retour à l'Accueil
            </a>
        </div>
    </div>
</div>

<?php require dirname(__FILE__) . '/../_templates/_footer.html.php'; ?>