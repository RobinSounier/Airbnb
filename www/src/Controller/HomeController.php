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

use App\Entity\Role;
use App\Entity\Room;
use App\Repository\RoleRepository;
use App\Repository\RoomRepository;
use JulienLinard\Core\Controller\Controller;
use JulienLinard\Core\Middleware\CsrfMiddleware;
use JulienLinard\Router\Attributes\Route;
use JulienLinard\Router\Request;
use JulienLinard\Router\Response;
use JulienLinard\Auth\AuthManager;
use JulienLinard\Auth\Middleware\GuestMiddleware;
use JulienLinard\Auth\Middleware\AuthMiddleware;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Core\Form\Validator;
use JulienLinard\Core\Session\Session;
use App\Entity\User;
use App\Repository\UserRepository;

class HomeController extends Controller
{

    public function __construct(
        private AuthManager $auth,
        private EntityManager $em,
        private Validator $validator
    ) {}
    /**
     * Route racine : affiche la page d'accueil
     *
     * CONCEPT : Route simple sans middleware
     */
    #[Route(path: '/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        // Récupération de toutes les annonces
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $rooms = $roomRepo->findAllRooms(); // Utilisation de notre nouvelle méthode

        return $this->view('home/index', [
            'title' => 'Airbnb - Locations de vacances',
            'auth' => $this->auth,
            'rooms' => $rooms // On passe les données à la vue
        ]);
    }

    #[Route(path: '/register', name: 'registerGET', methods: ['GET'],  middleware: [new GuestMiddleware()])]
    public function registerForm(): Response
    {
        return $this->view('auth/register', [
            'title' => 'Devenir hôte - Airbnb',
        ]);
    }

    #[Route(path: '/register', name: 'register.post', methods: ['POST'], middleware: [new GuestMiddleware()])]
    public function register(Request $request): Response
    {
        $email = $request->getBodyParam('email', '');
        $password = $request->getBodyParam('password', '');
        $first_name = $request->getBodyParam('first_name', '');
        $last_name = $request->getBodyParam('last_name', '');

        // Validation
        $errors = [];

        if (!$this->validator->required($email)) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!$this->validator->email($email)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        } else {
            // Vérifier si l'email existe déjà
            $userRepo = $this->em->createRepository(UserRepository::class, User::class);
            if ($userRepo->emailExists($email)) {
                $errors['email'] = 'Cet email est déjà utilisé';
            }
        }

        if (!$this->validator->required($password)) {
            $errors['password'] = 'Le mot de passe est requis';
        } elseif (!$this->validator->min($password, 8)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères';
        }



        if (!$this->validator->required($first_name)) {
            $errors['first_name'] = 'Le prénom est requis';
        }

        if (!$this->validator->required($last_name)) {
            $errors['last_name'] = 'Le nom est requis';
        }

        if (!empty($errors)) {
            Session::flash('error', 'Veuillez corriger les erreurs du formulaire');
            return $this->view('auth/register', [
                'title' => 'Inscription',
                'errors' => $errors,
                'old' => [
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]
            ]);
        }


        // Créer l'utilisateur
        try {

            $user = new User();
            $user->email = $email;
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->role = 'user'; // Rôle par défaut
            $user->created_at = new \DateTime();

            $this->em->persist($user);
            $this->em->flush();

            // Connecter automatiquement l'utilisateur après l'inscription
            $this->auth->login($user);

            Session::flash('success', 'Inscription réussie ! Bienvenue !');
            return $this->redirect('/');
        } catch (\Exception $e) {
            Session::flash('error', 'Une erreur est survenue lors de l\'inscription');
            var_dump($e->getMessage());
            return $this->view('auth/register', [
                'title' => 'Inscription',
                'old' => [
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]
            ]);
        }
    }

    #[Route(path: '/login', name: 'loginGET', methods: ['GET'],  middleware: [new GuestMiddleware()])]
    public function loginForm(): Response
    {
        return $this->view('auth/login', [
            'title' => 'Connexion - Airbnb',
        ]);
    }

    #[Route(path: '/login', name: 'login.post', methods: ['POST'], middleware: [new GuestMiddleware()])]
    public function login(Request $request): Response
    {
        $email = $request->getBodyParam('email', '');
        $password = $request->getBodyParam('password', '');

        // Validation
        $errors = [];

        if (!$this->validator->required($email)) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!$this->validator->email($email)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        }

        if (!$this->validator->required($password)) {
            $errors['password'] = 'Le mot de passe est requis';
        }

        if (!empty($errors)) {
            Session::flash('error', 'Veuillez corriger les erreurs du formulaire');
            return $this->view('auth/login', [
                'title' => 'Connexion',
                'errors' => $errors,
                'old' => ['email' => $email]
            ]);
        }

        // Tentative d'authentification
        $credentials = [
            'email' => $email,
            'password' => $password
        ];

        if ($this->auth->attempt($credentials, (bool)false)) {
            Session::flash('success', 'Connexion réussie !');
            return $this->redirect('/');
        }

        Session::flash('error', 'Email ou mot de passe incorrect');
        return $this->view('auth/login', [
            'title' => 'Connexion',
            'old' => ['email' => $email]
        ]);
    }

    #[Route(path: '/logout', name: 'logout.post', methods: ['POST'], middleware: [new AuthMiddleware(), new CsrfMiddleware()])]
    public function logout(): Response
    {
        $this->auth->logout();
        Session::flash('success', 'Vous avez été déconnecté avec succès.');
        return $this->redirect('/');
    }


}