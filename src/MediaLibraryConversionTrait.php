<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Media;

trait MediaLibraryConversionTrait
{
    use HasMediaTrait;

    public function getMediaConversions($field = null)
    {
        $conversions = $field ? ($this->mediaConversionSettings[$field] ?? []) : [];

        $converts = [];
        foreach ($conversions as $key => $value) {
            $name = $this->convertsConversionName($field, $key);
            $converts[$name] = $value;
        }

        return $converts;
    }

    public function convertsConversionName($field, $key = '')
    {
        if ($key === '') {
            return '';
        }

        $model = static::class;
        $md5 = md5($model.'.'.$field);
        return substr($md5, 0, 4).substr($md5, -4, 4).'.'.$key;
    }

    public function registerMediaConversions(Media $media = null)
    {
        $conversions = $media->field ? $media->conversions : null;
        if (!$conversions || $media->field) {
            $media->conversions = $conversions = array_replace($conversions ?? [], config('medialibrary.conversions'), $this->getMediaConversions($media->field));
            if ($media->field) {
                $media->save();
            }
        }
        foreach ($conversions as $name => $manipulation) {
            $mediaConversion = $this->addMediaConversion($name);
            foreach ($manipulation as $method => $arguments) {
                if (!is_array($arguments)) {
                    $arguments = explode(',', (string) $arguments);
                }
                $arguments = array_map('trim', $arguments);

                call_user_func_array([$mediaConversion, $method], $arguments);
            }
        }
    }
}
