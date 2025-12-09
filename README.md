# 🏠 Projet Clone AirBnb - Analyse Architecturale et Technique

## ✨ Description et Objectif du Projet

Ce projet est une **plateforme de réservation d'hébergements** (clone AirBnb) développée en PHP, destinée à simuler les fonctionnalités principales d'une application de location courte durée.

Son objectif principal est de démontrer la maîtrise d'une **architecture logicielle propre et maintenable** basée sur le patron de conception **MVC (Modèle-Vue-Contrôleur)**, en utilisant un framework PHP moderne et léger (le JulienLinard PHP Framework).

L'intégralité de l'environnement de développement et de production est **conteneurisée via Docker** pour assurer l'**isolation et la reproductibilité** de l'infrastructure logicielle.

***

## 🏛 Architecture et Principes de Conception

### Patron de Conception : MVC

L'application est rigoureusement structurée selon le modèle **MVC** :

1.  **Contrôleurs (`src/Controller/`)** : Gèrent la logique des requêtes HTTP (ex: `AnnonceController.php`, `ReservationController.php`). Ils interagissent avec les Repositories et les Services pour récupérer ou traiter les données.
2.  **Modèles (via ORM)** : Les **Entités** (`src/Entity/`) représentent les objets métier (ex: `Room.php`, `Reservation.php`, `User.php`) et les **Repositories** (`src/Repository/`) encapsulent la logique d'accès à la base de données.
3.  **Vues (`views/`)** : Fichiers PHP/HTML responsables de l'affichage final (`showRoom.html.php`, `login.html.php`).

### Séparation des Préoccupations (SoC)

Le projet utilise des **Services** (`src/Service/`) pour la logique métier transverse, garantissant une bonne séparation des préoccupations :
* **Paiement :** Géré par un service dédié comme `StripePayment.php`.
* **Fichiers :** Géré par `UploadService.php` ou `FileUploadService.php` pour l'isolation des opérations I/O.

### Aperçu Visuel / Modélisation

La conception du système est formalisée par des diagrammes qui expliquent les relations entre les données et la logique métier :

* **Modèle Conceptuel de Données (MCD)**
* **Modèle Physique de Données (MPD)**
* **Cas d'Utilisation et Dictionnaire de Données**

***

## 💻 Technologie et Composants Clés

Le projet s'appuie sur une pile technologique moderne et robuste, gérée par **Composer** (gestionnaire de dépendances PHP).

| Composant | Technologie | Rôle Architectural | Source du Service |
| :--- | :--- | :--- | :--- |
| **Serveur Web/Runtime** | **Apache / PHP 8.1+** | Exécute l'application dans un conteneur dédié (`apache_airbnb`). | `apache/Dockerfile` |
| **Base de Données** | **MariaDB 11.3** | Stockage des données d'hébergement et des utilisateurs. | `mariadb:11.3` |
| **ORM & Migrations** | **Doctrine PHP** | Couche d'abstraction pour l'accès aux données (Entités) et gestion du schéma BDD (migrations). | `julienlinard/doctrine-php` |
| **Routing** | **PHP Router** | Mappage des URL aux Contrôleurs et gestion des middlewares. | `julienlinard/php-router` |
| **Authentification** | **Auth PHP** | Gestion sécurisée des sessions utilisateur et de l'accès (Guard/Middleware). | `julienlinard/auth-php` |
| **Conteneurisation**| **Docker Compose** | Orchestration de l'environnement de développement (expose HTTP sur **8082** et MariaDB sur **3308**). | `docker-compose.yml` |

***

## ⚙️ Dépendances Techniques et Configuration

### Variables d'Environnement

Le projet utilise un fichier `.env` pour stocker les configurations spécifiques à l'environnement, garantissant que les identifiants et secrets ne sont pas committés.

| Variable | Usage | Exemple de valeur |
| :--- | :--- | :--- |
| `MARIADB_CONTAINER` | Hôte interne de la BDD pour l'application PHP. | `mariadb_app` |
| `MYSQL_DATABASE` | Nom de la base de données de l'application. | `app_db` |
| `APP_SECRET` | Clé secrète utilisée pour les sessions et la sécurité (CSRF). **Cruciale pour la sécurité.** | (Chaîne aléatoire de 32+ caractères) |
| `APP_DEBUG` | Active le mode débogage du framework (1 ou 0). | `1` |

### Pipeline d'Initialisation (Rôle et Séquence)

L'initialisation du projet est une séquence critique qui garantit que l'environnement est prêt pour le développement :

1.  **Démarrage des Services :** `docker-compose up -d` (crée les conteneurs et attache les volumes).
2.  **Installation des Dépendances :** `composer install` (télécharge et mappe les packages Framework/ORM/Auth).
3.  **Initialisation du Schéma BDD :** `doctrine-migrate migrate` (crée les tables à partir des Entités).

```bash
# Séquence de démarrage pour les architectes
docker-compose up -d
docker exec -it apache_airbnb sh -c "composer install"
docker exec -it apache_airbnb vendor/bin/doctrine-migrate migrate
```
## ✅ Bonnes Pratiques et Conventions

**Architecture Modulaire (MVC)** : L'organisation stricte en `Controller`, `Entity` et `views` garantit une séparation claire des responsabilités (SoC) et facilite la navigation et la maintenabilité du code.

**Accès aux Données (Repository Pattern)** : La logique d'accès à la BDD est isolée dans des classes `Repository` dédiées, ce qui favorise la testabilité des requêtes et la clarté du Modèle.

**Configuration Dynamique et Séparée (.env)** : Les paramètres sensibles et spécifiques à l'environnement (clés de BDD, `APP_SECRET`) sont externalisés dans le fichier `www/.env`, assurant qu'ils ne sont pas inclus dans le contrôle de version.

**Environnement Isolé et Reproductible (Docker)** : L'utilisation de **Docker Compose** assure un environnement de développement *immutable* et reproductible pour tous les développeurs.

**Vérification de Santé des Services (Healthchecks)** : Des **healthchecks** sont définis dans le `docker-compose.yml` pour les services MariaDB et Apache, afin de garantir qu'ils sont pleinement opérationnels avant que l'application ne tente de se connecter.

**Abstraction des Services Métier** : La logique métier externe ou complexe (ex. gestion des paiements ou uploads) est isolée dans des classes `Service` dédiées (comme `StripePayment.php` ou `UploadService.php`), facilitant leur modification ou leur remplacement.

