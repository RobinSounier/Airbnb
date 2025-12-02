<?php

/**
 * ============================================
 * UPLOAD RESULT
 * ============================================
 *
 * Classe pour représenter le résultat d'un upload
 * Pattern Value Object pour une meilleure gestion des résultats
 */

declare(strict_types=1);

namespace App\Service;

class UploadResult
{
    private bool $success;
    private ?array $data = null;
    private ?string $error = null;
    private array $uploaded = [];
    private array $errors = [];

    /**
     * Constructeur privé - utiliser les méthodes statiques
     */
    private function __construct(bool $success, ?array $data = null, ?string $error = null, array $uploaded = [], array $errors = [])
    {
        $this->success = $success;
        $this->data = $data;
        $this->error = $error;
        $this->uploaded = $uploaded;
        $this->errors = $errors;
    }

    /**
     * Crée un résultat de succès pour un fichier unique
     *
     * @param array $data Données du fichier uploadé
     * @return self
     */
    public static function success(array $data): self
    {
        return new self(true, $data);
    }

    /**
     * Crée un résultat d'erreur
     *
     * @param string $error Message d'erreur
     * @return self
     */
    public static function error(string $error): self
    {
        return new self(false, null, $error);
    }

    /**
     * Crée un résultat pour plusieurs fichiers
     *
     * @param array $uploaded Fichiers uploadés avec succès
     * @param array $errors Erreurs par fichier
     * @return self
     */
    public static function multiple(array $uploaded, array $errors = []): self
    {
        $success = !empty($uploaded) && empty($errors);
        return new self($success, null, null, $uploaded, $errors);
    }

    /**
     * Vérifie si l'upload a réussi
     *
     * @return bool True si succès
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Vérifie si l'upload a échoué
     *
     * @return bool True si erreur
     */
    public function isError(): bool
    {
        return !$this->success;
    }

    /**
     * Récupère les données du fichier uploadé (pour un fichier unique)
     *
     * @return array|null Données du fichier ou null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Récupère le message d'erreur (pour un fichier unique)
     *
     * @return string|null Message d'erreur ou null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Récupère les fichiers uploadés avec succès (pour plusieurs fichiers)
     *
     * @return array Tableau de fichiers uploadés
     */
    public function getUploaded(): array
    {
        return $this->uploaded;
    }

    /**
     * Récupère les erreurs par fichier (pour plusieurs fichiers)
     *
     * @return array Tableau d'erreurs [['file' => string, 'error' => string], ...]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Vérifie s'il y a des fichiers uploadés
     *
     * @return bool True si au moins un fichier a été uploadé
     */
    public function hasUploaded(): bool
    {
        return !empty($this->uploaded) || $this->data !== null;
    }

    /**
     * Vérifie s'il y a des erreurs
     *
     * @return bool True si au moins une erreur
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors) || $this->error !== null;
    }

    /**
     * Récupère tous les messages d'erreur sous forme de chaîne
     *
     * @return string Messages d'erreur concaténés
     */
    public function getErrorsAsString(): string
    {
        if ($this->error !== null) {
            return $this->error;
        }

        if (empty($this->errors)) {
            return '';
        }

        $messages = [];
        foreach ($this->errors as $error) {
            $messages[] = ($error['file'] ?? 'Fichier') . ': ' . ($error['error'] ?? 'Erreur inconnue');
        }

        return implode(', ', $messages);
    }
}