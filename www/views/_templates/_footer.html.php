<?php $currentYear = date('Y'); ?>
<footer class="mt-auto bg-white border-t border-gray-200 shadow-[0_-1px_6px_rgba(0,0,0,0.05)] pt-10 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-8 mb-10">

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-4">SUPPORT</h3>
                <ul class="space-y-3">
                    <li class="text-sm text-gray-600">Centre d'aide</li>
                    <li class="text-sm text-gray-600">AirCover</li>
                    <li class="text-sm text-gray-600">Options d'annulation</li>
                    <li class="text-sm text-gray-600">Signaler un problème</li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-4">HÉBERGEMENT</h3>
                <ul class="space-y-3">
                    <li class="text-sm text-gray-600">Devenir hôte</li>
                    <li class="text-sm text-gray-600">Centre de ressources</li>
                    <li class="text-sm text-gray-600">Forum de la communauté</li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-800 mb-4">AIRBNB</h3>
                <ul class="space-y-3">
                    <li class="text-sm text-gray-600">Actualités</li>
                    <li class="text-sm text-gray-600">Carrières</li>
                    <li class="text-sm text-gray-600">Investisseurs</li>
                    <li class="text-sm text-gray-600">Cartes cadeaux</li>
                </ul>
            </div>

            <div class="hidden lg:block">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">DÉCOUVRIR</h3>
                <ul class="space-y-3">
                    <li class="text-sm text-gray-600">Confiance et sécurité</li>
                    <li class="text-sm text-gray-600">Airbnb Magazine</li>
                    <li class="text-sm text-gray-600">Diversité et Inclusion</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <div class="flex flex-col sm:flex-row justify-between items-center text-xs text-gray-600">

                <div class="order-2 sm:order-1 flex flex-wrap justify-center sm:justify-start space-x-3 mb-3 sm:mb-0">
                    <span class="mr-2">© <?= $currentYear ?> Airbnb, Inc.</span>
                    <span class="text-gray-600 whitespace-nowrap">Confidentialité</span>
                    <span class="mx-1">&middot;</span>
                    <span class="text-gray-600 whitespace-nowrap">Conditions</span>
                    <span class="mx-1">&middot;</span>
                    <span class="text-gray-600 whitespace-nowrap">Plan du site</span>
                </div>

                <div class="order-1 sm:order-2 flex items-center space-x-6 mb-4 sm:mb-0 font-medium">
                    <span class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l-6-6m0 0l6-6m-6 6h18" />
                        </svg>
                        Français (FR)
                    </span>

                    <span class="text-gray-600">
                        EUR
                    </span>
                </div>
            </div>
        </div>

    </div>
</footer>
</body>
</html>