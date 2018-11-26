<?php
Route::group(['prefix' => config('medialibrary.prefix_url')], function() {
    Route::get(
        'open/{editor?}',
        [
            'as' => 'livecms.media-library.open',
            'uses' => '\LiveCMS\MediaLibrary\MediaLibraryController@open'
        ]
    );
    Route::get(
        '{type}/get/{limit?}',
        [
            'as' => 'livecms.media-library.get',
            'uses' => '\LiveCMS\MediaLibrary\MediaLibraryController@get'
        ]
    );
    Route::post(
        '{type}/upload',
        [
            'as' => 'livecms.media-library.upload',
            'uses' => '\LiveCMS\MediaLibrary\MediaLibraryController@upload'
        ]
    );
    Route::put(
        '{type}/{identifier}/rename',
        [
            'as' => 'livecms.media-library.rename',
            'uses' => '\LiveCMS\MediaLibrary\MediaLibraryController@rename'
        ]
    );
    Route::delete(
        '{type}/{identifier}/delete', 
        [
            'as' => 'livecms.media-library.delete',
            'uses' => '\LiveCMS\MediaLibrary\MediaLibraryController@delete'
        ]
    );
});