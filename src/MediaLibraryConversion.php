<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

class MediaLibraryConversion implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return 'files/'.$media->sha1_checksum.'_'.$media->size.'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'c/';
    }
}
