<?php

namespace App\Http\Controllers\Web;

use App\Enums\PermissionType;
use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class DocumentViewController extends Controller
{
    public function view(string $id, Request $request)
    {
        \Log::info('DocumentViewController@view called', [
            'id' => $id,
            'path' => $request->path(),
            'url' => $request->url(),
            'query' => $request->query()
        ]);
        
        // Lấy token từ query string
        $token = $request->query('token');
        
        if (!$token) {
            abort(401, 'Token is required');
        }

        // Verify token
        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) {
            abort(401, 'Invalid token');
        }

        // Lấy user từ token
        $user = $accessToken->tokenable;
        if (!$user) {
            abort(401, 'Invalid user');
        }

        // Kiểm tra permission
        if (!$user->hasPermission(PermissionType::DOCUMENT_VIEW)) {
            abort(403, 'You do not have permission to view this document');
        }

        try {
            $document = Document::findOrFail($id);
            
            if (!Storage::disk('upload')->exists($document->file_path)) {
                abort(404, 'File not found');
            }

            // Trả về file để xem trong browser (inline)
            return Storage::disk('upload')->response(
                $document->file_path,
                $document->file_name,
                [
                    'Content-Type' => $document->mime_type,
                    'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
                ]
            );
        } catch (\Exception $error) {
            abort(500, 'Error loading document');
        }
    }
}

