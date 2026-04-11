<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Service d'upload et de gestion des images du bundle.
 */
class ImageUploadService
{
    public function __construct(
        private readonly string           $uploadDir,
        private readonly SluggerInterface $slugger,
    ) {}

    /**
     * Téléverse un fichier image, retourne le nom de fichier généré.
     *
     * @throws \RuntimeException si l'upload échoue
     */
    public function upload(UploadedFile $file, string $subDirectory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename     = $this->slugger->slug($originalFilename)->lower();
        $extension        = $file->guessExtension() ?? 'jpg';
        $newFilename      = $safeFilename . '-' . uniqid('', true) . '.' . $extension;

        $targetDir = $this->uploadDir . ($subDirectory ? '/' . $subDirectory : '');

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new \RuntimeException("Impossible de créer le répertoire d'upload : {$targetDir}");
        }

        try {
            $file->move($targetDir, $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage(), 0, $e);
        }

        return ($subDirectory ? $subDirectory . '/' : '') . $newFilename;
    }

    /**
     * Supprime un fichier image existant.
     */
    public function delete(string $filename): bool
    {
        $path = $this->uploadDir . '/' . $filename;
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Retourne le chemin absolu d'une image.
     */
    public function getPath(string $filename): string
    {
        return $this->uploadDir . '/' . $filename;
    }

    /**
     * Vérifie si une image existe.
     */
    public function exists(string $filename): bool
    {
        return file_exists($this->getPath($filename));
    }
}
