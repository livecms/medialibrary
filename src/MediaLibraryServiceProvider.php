<?php

namespace LiveCMS\MediaLibrary;

use Spatie\MediaLibrary\MediaLibraryServiceProvider as ServiceProvider;

class MediaLibraryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config/medialibrary.php' => config_path('medialibrary.php'),
        ], 'config');
    }

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/medialibrary.php', 'medialibrary');
    }

}
