<?php

/**
 * ============================================
 * FILE UPLOAD SERVICE
 * ============================================
 *
 * Service moderne et robuste pour gérer l'upload de fichiers multiples
 * Architecture orientée objet avec gestion d'erreurs avancée
 */

declare(strict_types=1);

namespace App\Service;

class FileUploadService
{
    private string $uploadPath;
    private array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private int $maxFileSize = 10 * 1024 * 1024; // 10MB
    private int $maxFiles = 10; // Nombre maximum de fichiers par upload

    public function __construct()
    {
        $this->uploadPath = dirname(__DIR__, 2) . '/public/uploads/';

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload un fichier unique
     *
     * @param array $file Données du fichier ($_FILES['field_name'])
     * @return UploadResult Résultat de l'upload
     */
    public function upload(array $file): UploadResult
    {
        // Vérifier si le fichier est fourni
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return UploadResult::error('Aucun fichier fourni');
        }

        // Vérifier que c'est un fichier uploadé valide
        if (!is_uploaded_file($file['tmp_name'])) {
            return UploadResult::error('Fichier non valide ou corrompu');
        }

        // Vérifier les erreurs d'upload PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return UploadResult::error($this->getUploadErrorMessage($file['error']));
        }

        // Validation de la taille
        if ($file['size'] > $this->maxFileSize) {
            return UploadResult::error(
                sprintf('Le fichier dépasse la taille maximale de %d MB', $this->maxFileSize / 1024 / 1024)
            );
        }

        // Validation du type MIME
        $mimeType = $this->getMimeType($file['tmp_name']);
        if (!$this->isAllowedMimeType($mimeType)) {
            return UploadResult::error(sprintf('Type de fichier non autorisé: %s', $mimeType ?: 'inconnu'));
        }

        // Validation de l'extension
        $extension = $this->getExtension($file['name']);
        if (!$this->isAllowedExtension($extension)) {
            return UploadResult::error(sprintf('Extension non autorisée: .%s', $extension));
        }

        // Générer un nom de fichier unique
        $filename = $this->generateUniqueFilename($extension);
        $destination = $this->uploadPath . $filename;

        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return UploadResult::error('Impossible de déplacer le fichier uploadé vers le répertoire de destination');
        }

        // Vérifier que le fichier a bien été déplacé
        if (!file_exists($destination)) {
            return UploadResult::error('Le fichier n\'a pas pu être sauvegardé');
        }

        // Déterminer le type de média
        $type = $this->determineType($mimeType);

        return UploadResult::success([
            'filename' => $filename,
            'original_filename' => $file['name'],
            'path' => '/uploads/' . $filename,
            'size' => $file['size'],
            'mime_type' => $mimeType,
            'type' => $type
        ]);
    }

    /**
     * Upload plusieurs fichiers
     *
     * @param array $files Tableau de fichiers ($_FILES['field_name'])
     * @return UploadResult Résultat avec tous les fichiers uploadés et les erreurs
     */
    public function uploadMultiple(array $files): UploadResult
    {
        // Vérifier si des fichiers ont été fournis
        if (empty($files) || !isset($files['name'])) {
            return UploadResult::success([]);
        }

        // Normaliser le tableau de fichiers
        $normalizedFiles = $this->normalizeFilesArray($files);

        if (empty($normalizedFiles)) {
            return UploadResult::success([]);
        }

        // Vérifier le nombre maximum de fichiers
        if (count($normalizedFiles) > $this->maxFiles) {
            return UploadResult::error(
                sprintf('Nombre maximum de fichiers dépassé (%d fichiers maximum)', $this->maxFiles)
            );
        }

        $uploaded = [];
        $errors = [];

        foreach ($normalizedFiles as $index => $file) {
            $result = $this->upload($file);

            if ($result->isSuccess()) {
                $uploaded[] = $result->getData();
            } else {
                $errors[] = [
                    'file' => $file['name'] ?? 'Fichier ' . ($index + 1),
                    'error' => $result->getError()
                ];
            }
        }

        return UploadResult::multiple($uploaded, $errors);
    }

    /**
     * Normalise un tableau de fichiers (gère le cas d'un seul fichier ou plusieurs)
     *
     * @param array $files Tableau $_FILES
     * @return array Tableau normalisé de fichiers
     */
    private function normalizeFilesArray(array $files): array
    {
        // Si c'est un seul fichier (pas de tableau)
        if (isset($files['name']) && !is_array($files['name'])) {
            // Vérifier que ce n'est pas un fichier vide
            if (!isset($files['error']) || $files['error'] === UPLOAD_ERR_NO_FILE || empty($files['tmp_name'])) {
                return [];
            }
            return [$files];
        }

        // Si c'est un tableau de fichiers (multiple)
        if (isset($files['name']) && is_array($files['name'])) {
            $normalized = [];
            $count = count($files['name']);

            for ($i = 0; $i < $count; $i++) {
                // Ignorer les fichiers vides ou invalides
                if (!isset($files['error'][$i]) ||
                    $files['error'][$i] === UPLOAD_ERR_NO_FILE ||
                    empty($files['tmp_name'][$i])) {
                    continue;
                }

                $normalized[] = [
                    'name' => $files['name'][$i] ?? '',
                    'type' => $files['type'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i] ?? '',
                    'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$i] ?? 0
                ];
            }

            return $normalized;
        }

        return [];
    }

    /**
     * Récupère le type MIME d'un fichier
     *
     * @param string $filePath Chemin du fichier
     * @return string|null Type MIME ou null
     */
    private function getMimeType(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        // Utiliser finfo si disponible (plus fiable)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType ?: null;
        }

        // Fallback sur mime_content_type
        $mimeType = mime_content_type($filePath);
        return $mimeType !== false ? $mimeType : null;
    }

    /**
     * Récupère l'extension d'un fichier
     *
     * @param string $filename Nom du fichier
     * @return string Extension en minuscules
     */
    private function getExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Vérifie si un type MIME est autorisé
     *
     * @param string|null $mimeType Type MIME
     * @return bool True si autorisé
     */
    private function isAllowedMimeType(?string $mimeType): bool
    {
        if ($mimeType === null) {
            return false;
        }

        return in_array($mimeType, $this->allowedMimeTypes, true);
    }

    /**
     * Vérifie si une extension est autorisée
     *
     * @param string $extension Extension
     * @return bool True si autorisée
     */
    private function isAllowedExtension(string $extension): bool
    {
        return in_array($extension, $this->allowedExtensions, true);
    }

    /**
     * Génère un nom de fichier unique
     *
     * @param string $extension Extension du fichier
     * @return string Nom de fichier unique
     */
    private function generateUniqueFilename(string $extension): string
    {
        do {
            $filename = 'media_' . uniqid('', true) . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $path = $this->uploadPath . $filename;
        } while (file_exists($path));

        return $filename;
    }

    /**
     * Récupère le message d'erreur pour un code d'erreur d'upload PHP
     *
     * @param int $errorCode Code d'erreur UPLOAD_ERR_*
     * @return string Message d'erreur
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP (upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire (MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant sur le serveur',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier',
            default => 'Erreur inconnue lors de l\'upload'
        };
    }

    /**
     * Détermine le type de média à partir du MIME type
     *
     * @param string $mimeType Type MIME
     * @return string Type de média ('image', 'video', 'audio', 'document', 'other')
     */
    private function determineType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ], true)) {
            return 'document';
        }

        return 'other';
    }

    /**
     * Supprime un fichier
     *
     * @param string $filename Nom du fichier
     * @return bool True si supprimé avec succès
     */
    public function delete(string $filename): bool
    {
        $filePath = $this->uploadPath . basename($filename);

        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Supprime plusieurs fichiers
     *
     * @param array $filenames Tableau de noms de fichiers
     * @return array Tableau avec 'deleted' (array) et 'failed' (array)
     */
    public function deleteMultiple(array $filenames): array
    {
        $deleted = [];
        $failed = [];

        foreach ($filenames as $filename) {
            if ($this->delete($filename)) {
                $deleted[] = $filename;
            } else {
                $failed[] = $filename;
            }
        }

        return [
            'deleted' => $deleted,
            'failed' => $failed
        ];
    }

    /**
     * Définit les types MIME autorisés
     *
     * @param array $types Types MIME autorisés
     */
    public function setAllowedMimeTypes(array $types): void
    {
        $this->allowedMimeTypes = $types;
    }

    /**
     * Définit les extensions autorisées
     *
     * @param array $extensions Extensions autorisées (sans le point)
     */
    public function setAllowedExtensions(array $extensions): void
    {
        $this->allowedExtensions = array_map('strtolower', $extensions);
    }

    /**
     * Définit la taille maximale de fichier
     *
     * @param int $size Taille maximale en octets
     */
    public function setMaxFileSize(int $size): void
    {
        $this->maxFileSize = $size;
    }

    /**
     * Définit le nombre maximum de fichiers par upload
     *
     * @param int $maxFiles Nombre maximum de fichiers
     */
    public function setMaxFiles(int $maxFiles): void
    {
        $this->maxFiles = $maxFiles;
    }

    /**
     * Retourne le chemin d'upload
     *
     * @return string Chemin d'upload
     */
    public function getUploadPath(): string
    {
        return $this->uploadPath;
    }
}