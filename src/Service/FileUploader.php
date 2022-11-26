<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{

    /**
     * FileUploader constructor.
     * @param string $images_directory => voir config/services.yaml
     */
    public function __construct(
        private string $images_directory
    ) { }

    public function uploadFile(UploadedFile $uploadedFile, string $namespace = ''): string
    {
        $destination = $this->images_directory.$namespace;
        // On récupère le nom original de l'image
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        // On génere un nouveau nom de fichier
        $newFilename = $originalFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();
        // On déplace l'image
        $uploadedFile->move($destination, $newFilename);
        return $namespace.'/'.$newFilename;
    }

}