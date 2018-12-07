<?php

namespace LiveCMS\MediaLibrary;

use Illuminate\Support\Str;

trait HasMediaLibraryTrait
{
    use MediaLibraryConversionTrait;

    public function getMediaFields()
    {
        return array_keys($this->medias ?? []);
    }

    public function getMultipleMediaFields()
    {
        return $this->multipleMedias ?? [];
    }

    public function getNonReplaceableMediaFields()
    {
        return $this->nonReplaceableMedias ?? [];
    }

    public function isReplaceableMediaField($field)
    {
        return !in_array($field, $this->getNonReplaceableMediaFields());
    }

    public function getMediaCollections($field = null)
    {
        if ($field === null) {
            return array_merge(array_values($this->medias), ['default']);
        }
        return $this->medias[$field] ?? 'default';
    }

    public function getMediaData($field)
    {
        $media = $this->getMedia($this->getMediaCollections($field), function ($item) use ($field) {
            return $item->field == $field;
        });

        return !in_array($field, $this->getMultipleMediaFields()) ? $media->last() : $media;
    }

    public function __get($name)
    {
        $imageMethod = Str::studly('get '.$name.' Image');

        if (method_exists($this, $imageMethod)) {
            return $this->{$imageMethod}();
        }

        return parent::__get($name);
    }

    public function toArray()
    {
        $result = parent::toArray();
        foreach ($this->getMediaFields() as $field) {
            $result[$field] = $this->getMediaData($field);
        }

        return $result;
    }
}
