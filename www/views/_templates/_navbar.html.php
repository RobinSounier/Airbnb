<nav class="bg-[linear-gradient(180deg,#ffffff_39.9%,#f8f8f8_100%)]  flex items-center justify-between px-4">
        <img src="assets/images/Airbnb_Logo_Bélo.png" class="h-9 mt-4 mx-4" alt="">

        <form action="#" method="#">
            <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
            <div class="relative w-96">
                <input
                    type="search"
                    name="searchNavBar"
                    id="ShearchNavBar"
                    placeholder="Search..."
                    class="border border-gray-300 rounded-full py-2 pl-4 pr-12 w-full focus:outline-none focus:ring-2 focus:ring-red-400"
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
                <!-- Bouton pour les invités -->
                <form action="/login" method="GET">
                    <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
                    <button
                            type="submit"
                            class="bg-gray-100 text-gray-800 px-4 py-2 rounded-full hover:bg-gray-200 transition"
                    >
                        Se connecter
                    </button>
                </form>
                <form action="/register" method="GET">
                    <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
                    <button
                            type="submit"
                            class="bg-red-400 text-white px-4 py-2 rounded-full hover:bg-red-500 transition"
                    >
                        Devenir hôte
                    </button>
                </form>
            <?php else: ?>
                <!-- Boutons pour les utilisateurs connectés -->
                <form action="/mesAnnonces" method="GET">
                    <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
                    <button class="bg-gray-100 text-gray-800 px-4 py-2 rounded-full hover:bg-gray-200 transition">
                        Mes annonces
                    </button>
                </form>

                <form action="/logout" method="POST">
                    <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>
                    <button
                            type="submit"
                            class="bg-red-400 text-white px-4 py-2 rounded-full hover:bg-red-500 transition"
                    >
                        Se déconnecter
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </nav>



</nav>