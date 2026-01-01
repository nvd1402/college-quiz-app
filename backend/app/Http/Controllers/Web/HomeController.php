<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleType;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        try {
            $baseScoreScale = Setting::get('exam_base_score_scale');
            if ($baseScoreScale) {
                $this->queueCookie('base_score_scale', $baseScoreScale);
            }
        } catch (\Exception $error) {
            \Log::error('HomeController error: ' . $error->getMessage());
        }
        
        return view('index');
    }

    public function privacy()
    {
        return view('policy.privacy');
    }

    public function term()
    {
        return view('policy.term');
    }

    public function security()
    {
        return view('policy.security');
    }

    private function queueCookie($name, $value, $minutes = 30, $http_only = false)
    {
        Cookie::queue($name, $value, $minutes, null, null, false, $http_only);
    }
}
