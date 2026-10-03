<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageUploader
{
    public function __construct(
        private readonly string $publicImagesDirectory,
        private readonly SluggerInterface $slugger,
        private readonly Filesystem $filesystem,
    ) {
    }

    /**
     * Stores the uploaded file under the given subdirectory and returns the filename to persist (relative to public/images).
     */
    public function upload(UploadedFile $file, string $subdirectory): string
    {
        $safeName = $this->slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = sprintf('%s-%s.%s', $safeName, bin2hex(random_bytes(4)), $file->guessExtension() ?: 'bin');

        $file->move($this->publicImagesDirectory.'/'.$subdirectory, $filename);

        return $subdirectory.'/'.$filename;
    }

    public function remove(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $this->filesystem->remove($this->publicImagesDirectory.'/'.$filename);
    }
}
