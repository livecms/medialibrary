<?php

namespace LiveCMS\MediaLibrary;

interface HasMediaLibrary
{
    public function getMediaFields();

    public function getMediaCollections($field);
}
