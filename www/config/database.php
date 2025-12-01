<?php

/**
 * Configuration de la base de données
 * 
 * SÉCURITÉ : Les identifiants sensibles (user, password, database) DOIVENT
 * être définis dans le fichier .env et ne JAMAIS être en dur dans le code.
 * 
 * Seules les valeurs non sensibles peuvent avoir des valeurs par défaut.
 */

// Valeurs par défaut uniquement pour les paramètres non sensibles
$defaults = [
    'MARIADB_CONTAINER' => 'mariadb_app', // Nom du container Docker (non sensible)
    'MARIADB_PORT' => '3306', // Port par défaut MySQL (non sensible)
];

/**
 * Récupère une variable d'environnement avec une valeur par défaut optionnelle
 * 
 * @param string $key Clé de la variable d'environnement
 * @param string|null $default Valeur par défaut (null = obligatoire)
 * @return string Valeur de la variable d'environnement
 * @throws \RuntimeException Si la variable est obligatoire et non définie
 */
$getEnv = function(string $key, ?string $default = null) use ($defaults): string {
    $value = getenv($key);
    
    // Si la variable n'est pas définie ou vide
    if ($value === false || $value === '') {
        // Si une valeur par défaut existe (non sensible), l'utiliser
        if (isset($defaults[$key])) {
            return $defaults[$key];
        }
        
        // Si une valeur par défaut est fournie, l'utiliser
        if ($default !== null) {
            return $default;
        }
        
        // Sinon, la variable est obligatoire → lever une exception
        throw new \RuntimeException(
            "Variable d'environnement obligatoire non définie: {$key}. " .
            "Veuillez la définir dans votre fichier .env"
        );
    }
    
    return $value;
};

// Variables sensibles : DOIVENT être définies dans .env (pas de valeur par défaut)
$dbName = $getEnv('MYSQL_DATABASE');
$dbUser = $getEnv('MYSQL_USER');
$dbPassword = $getEnv('MYSQL_PASSWORD');

// Variables non sensibles : peuvent avoir des valeurs par défaut
// IMPORTANT : Dans Docker, le host doit être le nom du SERVICE Docker
// Le nom du service correspond au nom du conteneur configuré (MARIADB_CONTAINER)
$dbHost = $getEnv('MARIADB_CONTAINER', 'mariadb_app');
$dbPort = $getEnv('MARIADB_PORT', '3308');

// Convertir le port en int si c'est une string
$dbPort = is_numeric($dbPort) ? (int)$dbPort : 3308;

// Validation : s'assurer que le host n'est pas localhost en Docker
// (cela ne fonctionnerait pas car chaque container a son propre localhost)
if ($dbHost === 'localhost' || $dbHost === '127.0.0.1') {
    throw new \RuntimeException(
        "Le host de la base de données ne peut pas être 'localhost' ou '127.0.0.1' dans Docker. " .
        "Utilisez le nom du service Docker (qui correspond à MARIADB_CONTAINER) ou définissez MARIADB_CONTAINER dans votre .env"
    );
}
return [
    'driver' => 'mysql',
    'host' => $dbHost,
    'port' => $dbPort,
    'dbname' => $dbName,
    'user' => $dbUser,
    'password' => $dbPassword,
    'charset' => 'utf8mb4',
];