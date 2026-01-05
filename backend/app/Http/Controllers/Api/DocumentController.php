<?php

namespace App\Http\Controllers\Api;

use App\Enums\PermissionType;
use App\Helper\Reply;
use App\Http\Controllers\Controller;
use App\Http\Requests\Document\IndexRequest;
use App\Http\Requests\Document\StoreRequest;
use App\Http\Requests\Document\UpdateRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(IndexRequest $request)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::DOCUMENT_VIEW), 403);
        $validated = $request->validated();

        try {
            $data = Document::query();
            if (!empty($validated['search'])) {
                $data = $data->search($validated['search']);
            }
            $data = $data
                ->limit($this->defaultLimit)
                ->latest('id')
                ->get();
            return Reply::successWithData($data, '');
        } catch (\Exception $error) {
            return $this->handleException($error);
        }
    }

    public function store(StoreRequest $request)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::DOCUMENT_CREATE), 403);
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $filePath = $file->store('documents', 'upload');
            $fileSize = $this->formatFileSize($file->getSize());

            $document = Document::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $fileSize,
                'mime_type' => $file->getMimeType(),
            ]);

            DB::commit();
            return Reply::successWithData($document, trans('app.successes.record_save_success'));
        } catch (\Exception $error) {
            DB::rollBack();
            return $this->handleException($error);
        }
    }

    public function show(string $id)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::DOCUMENT_VIEW), 403);

        try {
            $document = Document::findOrFail($id);
            return Reply::successWithData($document, '');
        } catch (\Exception $error) {
            return $this->handleException($error);
        }
    }

    public function update(UpdateRequest $request, string $id)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::DOCUMENT_UPDATE), 403);
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $document = Document::findOrFail($id);
            $updateData = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
            ];

            // If a new file is uploaded, replace the old one
            if ($request->hasFile('file')) {
                // Delete old file
                if (Storage::disk('upload')->exists($document->file_path)) {
                    Storage::disk('upload')->delete($document->file_path);
                }

                $file = $request->file('file');
                $filePath = $file->store('documents', 'upload');
                $fileSize = $this->formatFileSize($file->getSize());

                $updateData['file_path'] = $filePath;
                $updateData['file_name'] = $file->getClientOriginalName();
                $updateData['file_size'] = $fileSize;
                $updateData['mime_type'] = $file->getMimeType();
            }

            $document->update($updateData);
            DB::commit();
            return Reply::successWithData($document, trans('app.successes.record_save_success'));
        } catch (\Exception $error) {
            DB::rollBack();
            return $this->handleException($error);
        }
    }

    public function destroy(string $id)
    {
        $user = $this->getUser();
        abort_if(!$user->hasPermission(PermissionType::DOCUMENT_DELETE), 403);

        DB::beginTransaction();
        try {
            $document = Document::findOrFail($id);
            
            // Delete file from storage
            if (Storage::disk('upload')->exists($document->file_path)) {
                Storage::disk('upload')->delete($document->file_path);
            }

            $document->delete();
            DB::commit();
            return Reply::successWithMessage(trans('app.successes.record_delete_success'));
        } catch (\Exception $error) {
            DB::rollBack();
            return $this->handleException($error);
        }
    }

    public function view(string $id, Request $request)
    {
        // Cho phép xem với token trong query string (để dùng trong iframe)
        $token = $request->query('token');
        $user = null;
        
        if ($token) {
            // Verify token từ query string
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if (!$accessToken) {
                abort(401, 'Invalid token');
            }
            $user = $accessToken->tokenable;
            if (!$user) {
                abort(401, 'Invalid user');
            }
        } else {
            // Fallback: sử dụng token từ header (normal API call)
            $user = auth('sanctum')->user();
            if (!$user) {
                abort(401, 'Unauthenticated');
            }
        }
        
        // Kiểm tra permission
        if (!$user->hasPermission(PermissionType::DOCUMENT_VIEW)) {
            abort(403, 'You do not have permission to view this document');
        }

        try {
            $document = Document::findOrFail($id);
            
            if (!Storage::disk('upload')->exists($document->file_path)) {
                return Reply::error(trans('app.errors.404'), 404);
            }

            // Trả về file để xem trong browser (inline) - không cho phép download
            return Storage::disk('upload')->response(
                $document->file_path,
                $document->file_name,
                [
                    'Content-Type' => $document->mime_type,
                    'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
                ]
            );
        } catch (\Exception $error) {
            return $this->handleException($error);
        }
    }

    public function download(string $id)
    {
        $user = $this->getUser();
        
        // Chỉ admin hoặc dev mới có thể tải xuống
        // Người dùng thường chỉ có thể xem (view) nhưng không tải xuống
        if (!$user->isAdmin() && !$user->hasPermission(PermissionType::DOCUMENT_DOWNLOAD)) {
            abort(403, 'Bạn không có quyền tải xuống tài liệu này');
        }

        try {
            $document = Document::findOrFail($id);
            
            if (!Storage::disk('upload')->exists($document->file_path)) {
                return Reply::error(trans('app.errors.404'), 404);
            }

            return Storage::disk('upload')->download(
                $document->file_path,
                $document->file_name,
                [
                    'Content-Type' => $document->mime_type,
                ]
            );
        } catch (\Exception $error) {
            return $this->handleException($error);
        }
    }

    /**
     * Format file size to human readable format
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
