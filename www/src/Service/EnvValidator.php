<?php

/**
 * ============================================
 * ENV VALIDATOR SERVICE
 * ============================================
 * 
 * Service de validation des variables d'environnement
 * Centralise toute la logique de validation pour une meilleure maintenabilité
 */

declare(strict_types=1);

namespace App\Service;

class EnvValidator
{
    /**
     * Valide toutes les variables d'environnement requises
     * 
     * @throws \RuntimeException Si une variable requise est manquante ou invalide
     */
    public static function validate(): void
    {
        self::validateAppSecret();
        self::validateAppLocale();
    }
    
    /**
     * Valide APP_SECRET
     * 
     * @throws \RuntimeException Si APP_SECRET est manquant ou trop court
     */
    private static function validateAppSecret(): void
    {
        $appSecret = getenv('APP_SECRET');
        
        if (empty($appSecret)) {
            throw new \RuntimeException(
                "APP_SECRET n'est pas défini dans votre fichier .env. " .
                "Ce secret est utilisé pour la sécurité (sessions, tokens CSRF, etc.). " .
                "Générez-en un avec: php -r 'echo bin2hex(random_bytes(32)) . PHP_EOL;'"
            );
        }
        
        if (strlen($appSecret) < 32) {
            throw new \RuntimeException(
                "APP_SECRET doit contenir au moins 32 caractères pour la sécurité. " .
                "Générez-en un nouveau avec: php -r 'echo bin2hex(random_bytes(32)) . PHP_EOL;'"
            );
        }
    }
    
    /**
     * Valide APP_LOCALE
     * 
     * @throws \RuntimeException Si APP_LOCALE n'est pas supportée
     */
    private static function validateAppLocale(): void
    {
        $appLocale = getenv('APP_LOCALE') ?: 'fr';
        $supportedLocales = ['fr', 'en', 'es'];
        
        if (!in_array($appLocale, $supportedLocales, true)) {
            throw new \RuntimeException(
                "Locale non supportée: '{$appLocale}'. " .
                "Locales supportées: " . implode(', ', $supportedLocales) . ". " .
                "Définissez APP_LOCALE dans votre fichier .env."
            );
        }
    }
}