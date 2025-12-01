<?php

/**
 * ============================================
 * BOOTSTRAP SERVICE
 * ============================================
 * 
 * Service de bootstrap de l'application
 * Centralise la configuration et l'initialisation pour une meilleure organisation
 */

declare(strict_types=1);

namespace App\Service;

use JulienLinard\Core\Application;
use JulienLinard\Core\ErrorHandler;
use JulienLinard\Core\Logging\SimpleLogger;

class BootstrapService
{
    /**
     * Configure le mode debug et les paramètres PHP
     * 
     * @param Application $app Instance de l'application
     * @return bool Mode debug activé ou non
     */
    public static function configureDebug(Application $app): bool
    {
        $debug = getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1';
        
        if (!defined('APP_DEBUG')) {
            define('APP_DEBUG', $debug);
        }
        
        $app->getConfig()->set('app.debug', $debug);
        error_reporting($debug ? E_ALL : 0);
        ini_set('display_errors', $debug ? '1' : '0');
        
        return $debug;
    }
    
    /**
     * Configure la sécurité des sessions
     */
    public static function configureSessionSecurity(): void
    {
        // cookie_httponly : Empêche l'accès au cookie via JavaScript (protection XSS)
        ini_set('session.cookie_httponly', '1');
        
        // cookie_samesite : Empêche l'envoi du cookie lors de requêtes cross-site (protection CSRF)
        ini_set('session.cookie_samesite', 'Strict');
        
        // use_strict_mode : Empêche la fixation de session (attaque de fixation de session)
        ini_set('session.use_strict_mode', '1');
        
        // cookie_secure : Uniquement en production avec HTTPS
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    }
    
    /**
     * Configure l'ErrorHandler avec logging
     * 
     * @param Application $app Instance de l'application
     * @param bool $debug Mode debug
     * @param string $viewsPath Chemin vers les vues
     * @return SimpleLogger Logger configuré
     */
    public static function configureErrorHandler(Application $app, bool $debug, string $viewsPath): SimpleLogger
    {
        $logFile = dirname($viewsPath) . '/storage/logs/app.log';
        $logDir = dirname($logFile);
        
        // Créer le répertoire de logs s'il n'existe pas
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                throw new \RuntimeException(
                    "Impossible de créer le répertoire de logs '{$logDir}'. " .
                    "Vérifiez les permissions du répertoire parent."
                );
            }
        }
        
        // Vérifier que le répertoire est accessible en écriture
        if (!is_writable($logDir)) {
            // Essayer de corriger les permissions
            @chmod($logDir, 0755);
            if (!is_writable($logDir)) {
                throw new \RuntimeException(
                    "Le répertoire de logs '{$logDir}' n'est pas accessible en écriture. " .
                    "Veuillez vérifier les permissions (chmod 755 recommandé)."
                );
            }
        }
        
        $logger = new SimpleLogger($logFile);
        $errorHandler = new \JulienLinard\Core\ErrorHandler($app, $logger, $debug, $viewsPath);
        $app->setErrorHandler($errorHandler);
        
        return $logger;
    }
}