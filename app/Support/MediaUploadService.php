<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Http\UploadedFile;

final class MediaUploadService
{
    public function register(UploadedFile $file, array $metadata = []): Media
    {
        return Media::registerUpload($file, $metadata);
    }
}
