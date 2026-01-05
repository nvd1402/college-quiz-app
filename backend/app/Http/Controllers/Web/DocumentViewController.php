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

            // Tạo URL để load PDF từ API (với token)
            $baseUrl = $request->getSchemeAndHttpHost();
            $pdfUrl = $baseUrl . '/api/documents/' . $id . '/view?token=' . urlencode($token);

            // Trả về view HTML wrapper với các biện pháp bảo vệ
            $response = response()->view('documents.view', [
                'document' => $document,
                'pdfUrl' => $pdfUrl,
            ]);
            
            // Remove CSP header từ middleware trước (nếu có)
            $response->headers->remove('Content-Security-Policy');
            
            // Thêm headers để tránh bị Chrome chặn
            // CSP: Cho phép inline scripts để chặn DevTools - QUAN TRỌNG: phải có 'unsafe-inline'
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; frame-src 'self' data: blob:; frame-ancestors 'self';");
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            
            return $response;
        } catch (\Exception $error) {
            abort(500, 'Error loading document');
        }
    }
}

