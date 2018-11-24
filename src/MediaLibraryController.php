<?php

namespace LiveCMS\MediaLibrary;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class MediaLibraryController extends Controller
{
    protected $baseRepository;

    public function __construct(MediaRepository $repository)
    {
        $this->baseRepository = $repository;
    }

    public function get(Request $request, $type, $page = 1)
    {
        $perPage = 20;
        $keyword = $request->get('key');
        $medias = $this->baseRepository->get($type, $keyword, $page, $perPage);
        $files = $medias->map(function ($item) {
            $newItem = clone $item;
            $newItem = $newItem->toArray();
            $newItem['size'] = $item->human_readable_size;
            $newItem['filetime'] = $item->created_at;
            $newItem['identifier'] = $item->id;
            return array_only($newItem, ['identifier', 'fullname', 'name', 'url', 'thumbnail', 'extension', 'size', 'filetime']);
        });
        $dir = ["files" => $files->toArray(), "total" => $medias->count(), "total_all" => $medias->total()];
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
