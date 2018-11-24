<?php

namespace LiveCMS\MediaLibrary;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Artisan;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\FileAdder\FileAdderFactory;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\Jobs\PerformConversions;

class MediaFactory
{
    public static function upload($key)
    {
        if (request()->file($key)->isValid()) {
            $file = request()->file($key);
            $mime = $file->getClientMimeType();
            $collectionName = array_first(explode('/', $mime));
            $subject = new File(['name' => $file->getClientOriginalName()]);

            File::saving(function ($model) use ($collectionName, $file) {
                app(EmptyFileAdder::class)
                    ->setSubject($model)
                    ->setFile($file)
                    ->toMediaCollection($collectionName);
            });

            File::saved(function ($model) {
                $media = $model->media->last();
                $media->file_id = $model->id;
                $media->save();
            });
            $subject->save();
            return $subject->media->last();

        }

        return false;
    }

    public static function clone($media, $model, $key, $collectionName = null, array $customProperties = [], array $properties = [])
    {
        $collectionName = $collectionName ?: $model->getMediaCollections($key);

        if ($collectionName == $media->collection_name) {
            $class = get_class($model);
            $properties = array_replace([
                            'created_at' => $media->created_at,
                            'original_id' => $media->id,
                            'file_id' => $media->file_id,
                            'field' => $key
                        ], $properties);

            $newMedia = $media->replicate(['model_id', 'model_type']);
            $newMedia->custom_properties = $customProperties;
            $newMedia->fill($properties);

            $class::saved(function ($model) use ($newMedia) {
                $model->media()->save($newMedia);
                $errorMessages = [];

                $profileCollection = ConversionCollection::createForMedia($newMedia)
                    ->reject(function (Conversion $conversion) use ($newMedia) {
                        return file_exists($newMedia->getPath($conversion->getName()));
                    });

                $job = new PerformConversions($profileCollection, $newMedia);

                if ($customQueue = config('medialibrary.queue_name')) {
                    $job->onQueue($customQueue);
                }

                app(Dispatcher::class)->dispatch($job);
            });
            $newMedia->save();

            return $newMedia;
        }

        return false;
    }
}
