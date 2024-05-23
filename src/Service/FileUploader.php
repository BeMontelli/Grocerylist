<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FileUploader
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function uploadRecipeThumbnail(UploadedFile $file): string
    {
        $slugger = new AsciiSlugger();
        $fileName = date("His-") . $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileExtension = $file->getClientOriginalExtension();
        $fileDir = $this->projectDir . '/public';
        $filePath = '/images/recipes/';
        $file->move($fileDir . $filePath, $fileName . '.' . $fileExtension);

        return $filePath . $fileName . '.' . $fileExtension;
    }

    public function deleteThumbnail($fileDir,$currentThumbnail)
    {
        $filePath = $fileDir . $currentThumbnail;
        if(is_dir($fileDir) && file_exists($filePath) && !empty($currentThumbnail)) unlink($filePath);
    }
}