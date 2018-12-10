<?php

namespace LiveCMS\MediaLibrary;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\HtmlString;

class MediaLibraryController extends Controller
{
    protected $baseRepository;

    public function __construct(MediaRepository $repository)
    {
        $this->baseRepository = $repository;
    }

    public function open(Request $request, $editor = null)
    {
        $csrf = csrf_token();
        $baseUrl = LC_Route('index');
        $dropzone = '';
        if ($maxSizeFileConfig = config('medialibrary.max_file_size')) {
            $size = $maxSizeFileConfig / (1024 * 1024);
            $dropzone = "$.fn.midia.defaultSettings.dropzone.maxFilesize = '{}'";
        }
        return new HtmlString(<<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{$csrf}">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title></title>
        <link rel="stylesheet" href="/vendor/midia/midia.css">
        <link rel="stylesheet" href="/vendor/midia/dropzone.css">
    </head>
    <body>
        <div id="midia-inline"></div>

        <script src="/vendor/midia/jquery.js"></script>
        <script src="/vendor/midia/dropzone.js"></script>
        <script src="/vendor/midia/clipboard.js"></script>
        <script src="/vendor/midia/midia.js"></script>
        <script>
            $.fn.midia.defaultSettings.identifier = 'identifier';
            $.fn.midia.defaultSettings.customLoadUrl = function (limit, key) {
                return '{$baseUrl}/media-library/image/get/' + limit + '?key=' + key;
            }
            $.fn.midia.defaultSettings.customUploadUrl = function () {
                return '{$baseUrl}/media-library/image/upload';
            }
            $.fn.midia.defaultSettings.customRenameUrl = function (file) {
                return '{$baseUrl}/media-library/image/' + file + '/rename';
            }
            $.fn.midia.defaultSettings.customDeleteUrl = function (file) {
                return '{$baseUrl}/media-library/image/' + file + '/delete';
            }

            {$dropzone}

            $("#midia-inline").midia({
                inline: true,
                base_url: '/',
                editor: '{$editor}'
            });
        </script>
    </body>
</html>
HTML
    );
    }

    public function get(Request $request, $type, $page = 1)
    {
        $perPage = 20;
        $keyword = $request->get('key');
        $medias = $this->baseRepository->get($type, $keyword, $page, $perPage);
        $total = $this->baseRepository->count();
        $files = $medias->map(function ($item) {
            $newItem = clone $item;
            $newItem = $newItem->toArray();
            $newItem['fullname'] = $item->file_name;
            $newItem['size'] = $item->human_readable_size;
            $newItem['filetime'] = $item->created_at->diffForHumans();
            $newItem['identifier'] = $item->id;
            return array_only($newItem, ['identifier', 'fullname', 'name', 'url', 'thumbnail', 'extension', 'size', 'filetime']);
        });
        $dir = ["files" => $files->toArray(), "total" => $medias->total(), "total_all" => $total];
        return Response::json($dir);;
    }

    public function upload(Request $request, $type)
    {
        if (MediaFactory::upload('file')) {
            return Response::json('success', 200);
        } else {
            return Response::json(__('Uploading failed'), 400);
        }
    }

    public function rename(Request $request, $type, $identifier)
    {
        if ($media = $this->baseRepository->find($identifier)) {
            $newName = $request->get('newName');
            if ($media->file && $media->file->rename($newName)) {
                return Response::json(['success' => $newMedia = $this->baseRepository->find($identifier)]);
            };
        }
        return Response::json(__('Renaming failed'), 400);
    }

    public function delete(Request $request, $type, $identifier)
    {
        if ($media = $this->baseRepository->find($identifier)) {
            if ($media->file && $media->file->delete()) {
                return Response::json(['data' => 'ok']);
            }
        }
        return Response::json(__('Deleting failed'), 400);
    }
}
