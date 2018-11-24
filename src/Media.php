<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\Media as BaseMedia;

class Media extends BaseMedia
{
    protected $appends = ['fullname', 'url', 'thumbnail', 'extension'];

    public function getFullnameAttribute()
    {
        return $this->file_name;
    }

    public function getUrlAttribute()
    {
        return $this->getFullUrl();
    }

    public function getThumbnailAttribute()
    {
        return $this->getFullUrl('thumb');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
