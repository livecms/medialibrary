<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

interface HasMediaLibrary extends HasMediaConversions
{
    public function getMediaFields();

    public function getMediaCollections($field);
}
