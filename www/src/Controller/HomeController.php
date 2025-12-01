<?php

/**
 * ============================================
 * HOME CONTROLLER
 * ============================================
 * 
 * CONCEPT PÉDAGOGIQUE : Controller simple
 * 
 * Ce contrôleur gère la route racine "/" et affiche la page d'accueil.
 */

declare(strict_types=1);

namespace App\Controller;

use JulienLinard\Core\Controller\Controller;
use JulienLinard\Router\Attributes\Route;
use JulienLinard\Router\Response;

class HomeController extends Controller
{
    /**
     * Route racine : affiche la page d'accueil
     * 
     * CONCEPT : Route simple sans middleware
     */
    #[Route(path: '/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->view('home/index', [
            'title' => 'Airbnb',
        ]);
    }
}