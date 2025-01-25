<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    private $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
                'api_key'    => $_ENV['CLOUDINARY_API_KEY'],
                'api_secret' => $_ENV['CLOUDINARY_API_SECRET'],
                'url' => [
                    'secure' => true
                ]
            ],
        ]);
    }

    public function uploadFeatureImage(string $source)
    {
        return $this->cloudinary->uploadApi()->upload($source, [
            'folder' => 'PostFeatureImage'
        ]);
    }

    public function uploadProfileImage($file)
    {
        return $this->cloudinary->uploadApi()->upload($file, [
            'folder' => 'ProfileImage'
        ]);
    }
}
