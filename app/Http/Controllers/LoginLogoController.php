<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginLogoController extends Controller
{
    protected function settingsPath(): string
    {
        return storage_path('app/login-settings.json');
    }

    protected function loadSettings(): array
    {
        $defaults = [
            'login_title' => 'โรงเรียนบ้านช่องพลี',
        ];

        $path = $this->settingsPath();
        if (!file_exists($path)) {
            return $defaults;
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            return $defaults;
        }

        return array_merge($defaults, $data);
    }

    protected function saveSettings(array $settings): void
    {
        $path = $this->settingsPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function edit()
    {
        $logoCandidates = ['png', 'jpg', 'jpeg'];
        $logoPath = null;
        $logoUrl = asset('images/school-logo-bcp.png');

        foreach ($logoCandidates as $ext) {
            $candidatePath = public_path("images/school-logo-bcp.$ext");
            if (file_exists($candidatePath)) {
                $logoPath = $candidatePath;
                $logoUrl = asset("images/school-logo-bcp.$ext");
                break;
            }
        }

        if ($logoPath) {
            $logoUrl .= '?v=' . filemtime($logoPath);
        }

        $settings = $this->loadSettings();

        return view('admin.login-logo', [
            'logoUrl' => $logoUrl,
            'loginTitle' => $settings['login_title'] ?? 'โรงเรียนบ้านช่องพลี',
        ]);
    }

    public function update(Request $request)
    {
        $action = $request->input('action');
        $rules = [
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'login_title' => ['nullable', 'string', 'max:120'],
        ];
        if ($action === 'logo') {
            $rules['logo'][0] = 'required';
        }
        if ($action === 'title') {
            $rules['login_title'][0] = 'required';
        }
        $request->validate($rules);

        $settings = $this->loadSettings();
        $loginTitle = $request->input('login_title');
        if (is_string($loginTitle)) {
            $loginTitle = trim($loginTitle);
            if ($loginTitle !== '') {
                $settings['login_title'] = $loginTitle;
            }
        }
        $this->saveSettings($settings);

        if ($request->hasFile('logo')) {
            $targetDir = public_path('images');
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            foreach (['png', 'jpg', 'jpeg'] as $ext) {
                $oldPath = $targetDir . DIRECTORY_SEPARATOR . "school-logo-bcp.$ext";
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $logo = $request->file('logo');
            $extension = strtolower($logo->getClientOriginalExtension());
            $extension = in_array($extension, ['png', 'jpg', 'jpeg'], true) ? $extension : 'png';
            $logo->move($targetDir, "school-logo-bcp.$extension");
        }

        $status = match ($action) {
            'logo' => 'อัปเดตโลโก้หน้าเข้าสู่ระบบเรียบร้อยแล้ว',
            'title' => 'อัปเดตชื่อหน้าเข้าสู่ระบบเรียบร้อยแล้ว',
            default => 'อัปเดตการตั้งค่าหน้าเข้าสู่ระบบเรียบร้อยแล้ว',
        };

        return back()->with('status', $status);
    }
}
