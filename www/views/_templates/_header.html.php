<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Application') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php
    // Afficher les messages flash (success et error) depuis la session
    // Les messages sont affichés en haut à droite avec auto-hide après 5 secondes
    use JulienLinard\Core\Session\Session;
    $headerSuccess = Session::getFlash('success');
    $headerError = Session::getFlash('error');
    ?>
    
    <?php if ($headerSuccess): ?>
    <div class="fixed top-4 right-4 z-50 max-w-md w-full">
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($headerSuccess) ?></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Auto-hide après 5 secondes
        setTimeout(() => {
            document.querySelector('.bg-green-50')?.parentElement?.remove();
        }, 5000);
    </script>
    <?php endif; ?>
    
    <?php if ($headerError): ?>
    <div class="fixed top-4 right-4 z-50 max-w-md w-full">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($headerError) ?></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Auto-hide après 5 secondes
        setTimeout(() => {
            document.querySelector('.bg-red-50')?.parentElement?.remove();
        }, 5000);
    </script>
    <?php endif; ?>

