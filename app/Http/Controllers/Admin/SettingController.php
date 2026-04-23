<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return inertia('Admin/Settings/Index', [
            'settings' => SiteSetting::allAsArray(),
        ]);
    }

    public function store(Request $request)
    {
        $settingKeys = [
            'site_title', 'bio', 'bio_short', 'profile_photo',
            'instagram_url', 'email', 'phone', 'location',
            'about_text', 'equipment',
        ];

        foreach ($settingKeys as $key) {
            if ($request->has($key)) {
                SiteSetting::set($key, $request->input($key));
            }
        }

        return back()->with('success', 'Paramètres sauvegardés.');
    }
}
