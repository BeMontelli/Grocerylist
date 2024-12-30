<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Entity\File;
use App\Entity\User;

class FileUploader
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function uploadFile($formFile, User $user) {
        $extension = $formFile->getClientOriginalExtension();
        $titleFile = str_replace(".".$extension, "", $formFile->getClientOriginalName());
        $filePath = $this->uploadRecipeThumbnail($formFile,$user);
                        
        $file = new File();
        $file->setTitle($titleFile);
        $file->setUrl($filePath);
        $file->setExtension($extension);
        $file->setUser($user);
        
        return $file;
    }

    public function uploadRecipeThumbnail(UploadedFile $file, User $user): string
    {
        $filePath = '/images/u/'.$user->getId().'/';
        return $this->uploadPublicFile($file, $filePath);
    }

    public function uploadPublicFile(UploadedFile $file, $filePath): string
    {
        $slugger = new AsciiSlugger();
        $fileName = date("His-") . $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileExtension = $file->getClientOriginalExtension();
        $fileDir = $this->projectDir . '/public';
        $file->move($fileDir . $filePath, $fileName . '.' . $fileExtension);

        return $filePath . $fileName . '.' . $fileExtension;
    }

    public function deleteThumbnail($fileDir,$currentThumbnail)
    {
        $filePath = $fileDir . $currentThumbnail;
        if(is_dir($fileDir) && file_exists($filePath) && !empty($currentThumbnail)) unlink($filePath);
    }
}