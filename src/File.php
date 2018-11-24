<?php

namespace LiveCMS\MediaLibrary;

use App\Services\Base\Model;

class File extends Model
{
    protected $fillable = ['name', 'remote', 'remote_conversions'];

    public function media()
    {
        return $this->morphMany(config('medialibrary.media_model'), 'model');
    }

    public function rename($newName)
    {
        try {
            $this->update(['name' => $newName]);
            foreach ($this->media as $media) {
                $oldName = $media->name;
                $extension = $media->extension;
                $media->name = pathinfo($newName, PATHINFO_FILENAME);;
                $media->file_name = $newName;
                $media->save();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete()
    {
        static::deleted(function ($file) {
            foreach ($this->media as $media) {
                $media->delete();
            }
        });

        return parent::delete();
    }
}
