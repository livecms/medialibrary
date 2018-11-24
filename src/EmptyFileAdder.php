<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\FileAdder\FileAdder;
use Spatie\MediaLibrary\Helpers\File;

class EmptyFileAdder extends FileAdder
{
    /**
     * @param string $diskName
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    protected function determineDiskName(string $diskName)
    {
        return parent::determineDiskName($diskName);
    }

    /**
     * @param string $collectionName
     * @param string $diskName
     *
     * @return \Spatie\MediaLibrary\Media
     *
     * @throws FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public function toMediaCollection(string $collectionName = 'default', string $diskName = '')
    {
        $checksum = sha1_file($this->pathToFile);

        $mediaClass = config('medialibrary.media_model');
        $exist = app($mediaClass)->where('sha1_checksum', $checksum)->count();

        if ($exist) {
            throw new \Exception(__('File is exist'), 1);
        }

        $media = parent::toMediaCollection($collectionName, $diskName);
        if ($media) {
            $media->sha1_checksum = $checksum;
            if ($media->exists ?? false) {
                $media->save();
            }
        }

        return $media;
    }
}
