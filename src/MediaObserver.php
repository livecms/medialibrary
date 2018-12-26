<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\Filesystem\Filesystem;

class MediaObserver
{
    public function creating(Media $media)
    {
        $media->setHighestOrderNumber();
    }

    public function updating(Media $media)
    {
        if ($media->file_name !== $media->getOriginal('file_name')) {
            app(Filesystem::class)->renameFile($media, $media->getOriginal('file_name'));
        }
    }

    public function updated(Media $media)
    {
        if (is_null($media->getOriginal('model_id'))) {
            return;
        }

        if ($media->manipulations !== json_decode($media->getOriginal('manipulations'))) {
            app(FileManipulator::class)->createDerivedFiles($media);
        }
    }

    public function deleting(Media $media)
    {
        if (Media::where('original_id', $media->id)->count()) {
            throw new \Exception("Can't delete this media because exist in your data / model.");
        }
    }

    public function deleted(Media $media)
    {
        if ($media->original_id === null) {
            app(Filesystem::class)->removeFiles($media);
        }
    }
}
