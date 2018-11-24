<?php

namespace LiveCMS\MediaLibrary;

use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaRepository as BaseRepository;

class MediaRepository extends BaseRepository 
{
    public function __construct(Media $model)
    {
        parent::__construct($model);
        $model::observe($this);
    }

    public function get($collectionName = null, $keyword = null, $page = 1, $perPage = 20)
    {
        $model = $this->model->whereNull('original_id')->whereNotNull('file_id');
        if ($collectionName) {
            $model->where('collection_name', $collectionName);
        }
        if ($keyword) {
            $model->where('name', 'like', '%'.$keyword.'%');
        }
       return $model->paginate($perPage, $columns = ['*'], $pageName = 'page', $page);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function renameChildren(Media $media)
    {
        $tableName = $media->getTable();
        DB::table($tableName)
            ->where('original_id', $media->id)
            ->update(['name' => $media->name, 'file_name' => $media->file_name]);
    }

    public function updating(Media $media)
    {
        if ($media->file_name !== $media->getOriginal('file_name')) {
            $this->renameChildren($media);
        }
    }

    public function deleteChildren(Media $media)
    {
        $tableName = $media->getTable();
        DB::table($tableName)
            ->where('original_id', $media->id)
            ->delete();
    }

    public function deleted(Media $media)
    {
        $this->deleteChildren($media);
    }
}
