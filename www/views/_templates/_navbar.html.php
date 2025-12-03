<nav class="bg-[linear-gradient(180deg,#ffffff_39.9%,#f8f8f8_100%)] flex items-center justify-between px-4 border-b border-gray-200">
    <a href="/">
        <img src="/assets/images/Airbnb_Logo_Bélo.png" class="h-9 mt-4 mx-4" alt="Logo">
    </a>

    <form action="#" method="#">
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

    <nav class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">

            <?php if (!$auth->check()): ?>
                <a href="/login" class="bg-gray-100 text-gray-800 px-4 py-2 rounded-full hover:bg-gray-200 transition font-medium text-sm">
                    Se connecter
                </a>

                <a href="/register" class="bg-red-400 text-white px-4 py-2 rounded-full hover:bg-red-500 transition font-medium text-sm">
                    S'inscrire
                </a>

            <?php else: ?>
                <?php
                $user = $auth->user();
                // On vérifie l'ID du rôle.
                // Sécurité : on vérifie si roles_id existe, sinon par défaut on considère user (1)
                $roleId = ($user->role && $user->role->id) ? $user->role->id : 1;
                ?>

                <?php if ($roleId === 2): ?>

                    <a href="/mesAnnonces" class="bg-gray-100 text-gray-800 px-4 py-2 rounded-full hover:bg-gray-200 transition font-medium text-sm">
                        Mes annonces
                    </a>

                <?php else: ?>

                    <a href="/room/create" class="bg-gray-100 text-gray-800 px-4 py-2 rounded-full hover:bg-gray-200 transition font-medium text-sm">
                        Devenir hôte
                    </a>

                <?php endif; ?>

                <form action="/logout" method="POST" class="inline">
                    <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
                    <button
                            type="submit"
                            class="bg-red-400 text-white px-4 py-2 rounded-full hover:bg-red-500 transition font-medium text-sm"
                    >
                        Se déconnecter
                    </button>
                </form>

            <?php endif; ?>

        </div>
    </nav>
</nav>