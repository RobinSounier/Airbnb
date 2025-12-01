<?php

/**
 * ============================================
 * EVENT LISTENER SERVICE
 * ============================================
 * 
 * Service de gestion des event listeners
 * Centralise l'enregistrement des listeners pour une meilleure organisation
 */

declare(strict_types=1);

namespace App\Service;

use JulienLinard\Core\Events\EventDispatcher;
use JulienLinard\Core\Logging\SimpleLogger;

class EventListenerService
{
    /**
     * Enregistre tous les event listeners de l'application
     * 
     * @param EventDispatcher $events Dispatcher d'événements
     * @param SimpleLogger $logger Logger pour les logs
     */
    public static function register(EventDispatcher $events, SimpleLogger $logger): void
    {
        // Listener pour les requêtes HTTP
        $events->listen('request.started', function(array $payload) use ($logger) {
            $request = $payload['request'];
            $logger->info('Request started', [
                'method' => $request->getMethod(),
                'path' => $request->getPath(),
                'query' => $request->getQueryParams(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        });
        
        // Listener pour les réponses HTTP
        $events->listen('response.sent', function(array $payload) use ($logger) {
            $response = $payload['response'];
            $logger->info('Response sent', [
                'status' => $response->getStatusCode()
            ]);
        });
        
        // Listener pour les exceptions
        $events->listen('exception.thrown', function(array $payload) use ($logger) {
            $exception = $payload['exception'];
            $logger->error('Exception thrown', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);
        });
    }
}