<?php
Route::group(['prefix' => config('medialibrary.prefix_url')], function() {
    Route::get(
        '{type}/get/{limit?}',
        [
            'as' => 'livecms.media-library.get',
            'uses' => '\App\Services\MediaLibrary\MediaLibraryController@get'
        ]
    );
    Route::post(
        '{type}/upload',
        [
            'as' => 'livecms.media-library.upload',
            'uses' => '\App\Services\MediaLibrary\MediaLibraryController@upload'
        ]
    );
    Route::put(
        '{type}/{identifier}/rename',
        [
            'as' => 'livecms.media-library.rename',
            'uses' => '\App\Services\MediaLibrary\MediaLibraryController@rename'
        ]
    );
    Route::delete(
        '{type}/{identifier}/delete', 
        [
            'as' => 'livecms.media-library.delete',
            'uses' => '\App\Services\MediaLibrary\MediaLibraryController@delete'
        ]
    );
});