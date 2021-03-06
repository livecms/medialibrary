<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\Media as BaseMedia;

class Media extends BaseMedia
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'manipulations' => 'array',
        'conversions' => 'array',
        'custom_properties' => 'array',
    ];

    protected $appends = ['fullname', 'url', 'thumbnail', 'extension'];

    /**
     * Get the url to a original media file.
     *
     * @param string $conversionName
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getUrl(string $conversionName = ''): string
    {
        $conversions = $this->conversions;
        $conversionName = array_key_exists($conversionName, $conversions ?? [])
                            ? $conversionName
                            : ($this->field && $conversionName != ''
                                ? $this->model->convertsConversionName($this->field, $conversionName)
                                : $conversionName
                            );
        return parent::getUrl($conversionName);
    }

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
