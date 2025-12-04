<nav class="bg-[linear-gradient(180deg,#ffffff_39.9%,#f8f8f8_100%)] flex items-center justify-between px-4 border-b border-gray-200 relative z-50">
    <a href="/">
        <img src="/assets/images/Airbnb_Logo_Bélo.png" class="h-9 mt-4 mx-4" alt="Logo">
    </a>

    <form action="/room/search" method="POST">
        <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
        <div class="relative w-96 hidden md:block">
            <input
                    type="search"
                    name="searchNavBar"
                    id="ShearchNavBar"
                    placeholder="Rechercher..."
                    class="border border-gray-300 rounded-full py-2 pl-4 pr-12 w-full focus:outline-none focus:ring-2 focus:ring-red-400 shadow-sm"
            >
            <button
                    type="submit"
                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-red-400 text-white w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-500 transition"
            >
                🔍
            </button>
        </div>
    </form>

    <div class="px-6 py-4">
        <div class="relative">
            <button
                    id="user-menu-btn"
                    class="flex items-center gap-3 border border-gray-300 rounded-full py-1.5 pl-3 pr-2 hover:shadow-md transition cursor-pointer bg-white"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>

                <div class="bg-gray-500 text-white rounded-full p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>

            <div
                    id="user-dropdown"
                    class="hidden absolute right-0 top-12 w-64 bg-white rounded-xl shadow-[0_6px_16px_rgba(0,0,0,0.12)] border border-gray-100 py-2 overflow-hidden z-50"
            >

                <?php if (!$auth->check()): ?>
                    <a href="/login" class="block w-full text-left px-4 py-3 hover:bg-gray-100 transition font-medium text-sm text-gray-800">
                        Se connecter
                    </a>
                    <a href="/register" class="block w-full text-left px-4 py-3 hover:bg-gray-100 transition font-medium text-sm text-gray-800">
                        S'inscrire
                    </a>

                <?php else: ?>
                    <?php
                    $user = $auth->user();
                    $currentRole = $user->role ?? 'user';
                    ?>

                    <a href="/mesReservations" class="block w-full text-left px-4 py-3 hover:bg-gray-100 transition font-medium text-sm text-gray-800">
                        Mes réservations
                    </a>

                    <?php if ($currentRole === 'hote'): ?>
                        <a href="/mesAnnonces" class="block w-full text-left px-4 py-3 hover:bg-gray-100 transition font-medium text-sm text-gray-800">
                            Mes annonces
                        </a>
                    <?php else: ?>
                        <a href="/room/create" class="block w-full text-left px-4 py-3 hover:bg-gray-100 transition font-medium text-sm text-gray-800">
                            Devenir hôte
                        </a>
                    <?php endif; ?>

                    <div class="border-t border-gray-200 my-1"></div>

                    <form action="/logout" method="POST" class="w-full">
                        <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
                        <button
                                type="submit"
                                class="block w-full text-left px-4 py-3 hover:bg-gray-100 transition font-medium text-sm text-red-500"
                        >
                            Se déconnecter
                        </button>
                    </form>

                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('user-menu-btn');
        const dropdown = document.getElementById('user-dropdown');

        // Ouvrir / Fermer au clic
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        // Fermer si on clique ailleurs sur la page
        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });
</script>