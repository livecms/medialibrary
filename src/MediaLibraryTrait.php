<?php

namespace LiveCMS\MediaLibrary;

use Illuminate\Support\Str;

trait MediaLibraryTrait
{
    protected function getMediaClass() {
        $class = config('medialibrary.media_model');
        return app($class);
    }

    protected function addMedia(HasMediaLibrary $model)
    {

        $medias = [];
        $oldMediaIds = [];

        $hasFiles = [];

        foreach ($model->getMediaFields() as $key) {
            if (request()->hasFile($key)) {
                if ($media = MediaFactory::upload($key)) {
                    $newMedia = $this->cloneMedia($media, $model, $key, $oldMediaIds);
                    $medias[] = $newMedia;
                }
            }

            if (($libraryId = request()->get('selecting__'.$key, false)) !== false) {
                if ($media = $this->getMediaClass()->find($libraryId)) {
                    $newMedia = $this->cloneMedia($media, $model, $key, $oldMediaIds);
                    $medias[] = $newMedia;
                }
            }

            if (request()->get('removing__'.$key, false) == '1') {
                $media = $model->getMediaData($key);
                if ($media instanceof Collection) {
                    $oldMediaIds = array_merge($oldMediaIds, $media->pluck('id')->toArray());
                } else {
                    $oldMediaIds[] = $media->id;
                }
            } 
        }

        $this->deleteOldMedia($oldMediaIds);

        return $medias;
    }

    protected function cloneMedia($media, $model, $key, &$oldMediaIds)
    {
        $redundant = false;
        $oldMedias = collect([$model->getMediaData($key) ?? []])->flatten();
        foreach ($oldMedias as $oldMedia) {
            if ($oldMedia->original_id == $media->id) {
                $redundant = true;
            }
        }

        if (!$redundant && !$media->original_id) {

            $newMedia = MediaFactory::clone(
                $media,
                $model,
                $key,
                $collectionName = $model->getMediaCollections($key),
                $customProperties = [
                    'title' => class_basename($model). ' - '.($model->name ?: $model->title),
                ]
            );

            if ($model->isReplaceableMediaField($key)) {
                $oldMediaIds = array_merge($oldMediaIds, $oldMedias->pluck('id')->toArray());
            }

            return $newMedia;
        }

        return false;
    }

    public function getOldMedia($media)
    {
        return $this->getMediaClass()
                    ->where('id', '<>', $media->id)
                    ->where('model_id', $media->model_id)
                    ->where('model_type', $media->model_type)
                    ->where('field', $media->field)
                    ->get();
    }

    public function deleteOldMedia(array $mediaIds  = [])
    {
        return $this->getMediaClass()->whereIn('id', $mediaIds)->delete();
    }
}

