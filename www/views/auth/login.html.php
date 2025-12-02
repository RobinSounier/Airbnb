<div class="min-h-screen flex items-center justify-center bg-[#FFF8F7] py-12 px-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

            <!-- Header Airbnb -->
            <div class="bg-gradient-to-r from-[#FF5A5F] to-[#FF7E82] px-6 py-8 text-center">
                <h2 class="text-3xl text-white font-bold">Connexion</h2>
                <p class="text-white/90 mt-2">Accédez à votre espace personnel</p>
            </div>

            <form class="p-8 space-y-6" action="/login" method="post">

                <?= \JulienLinard\Core\Middleware\CsrfMiddleware::field() ?>

                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-[#FF5A5F] p-4 rounded-r-lg flex">
                        <p class="text-sm text-red-700"><?= $error ?></p>
                    </div>
                <?php endif; ?>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="email">
                        Votre email
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="votre@email.com"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-2xl
                               focus:ring-2 focus:ring-[#FF5A5F] focus:border-transparent
                               transition-all outline-none text-gray-900 placeholder-gray-400"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="password">
                        Votre mot de passe
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="********"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-2xl
                               focus:ring-2 focus:ring-[#FF5A5F] focus:border-transparent
                               transition-all outline-none text-gray-900 placeholder-gray-400"
                    >
                </div>

                <!-- Submit -->
                <div>
                    <button
                        type="submit"
                        class="w-full px-6 py-3 bg-[#FF5A5F] hover:bg-[#E14C50] text-white rounded-2xl
                               font-semibold shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5"
                    >
                        Se connecter
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Pas encore de compte ?
                        <a href="./register" class="font-semibold text-[#FF5A5F] hover:text-[#E14C50] transition">
                            S’inscrire
                        </a>
                    </p>
                </div>

            </form>

        </div>
    </div>
</div>
