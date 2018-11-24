<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Media;

trait MediaLibraryConversionTrait
{
    use HasMediaTrait;

    public function getMediaConversions()
    {
        return $this->mediaConversionSettings ?? [];
    }

    public function registerMediaConversions(Media $media = null)
    {
        $conversions = array_replace(config('services.medialibrary.conversions'), $this->getMediaConversions());

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
