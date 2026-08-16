<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'system_name' => Setting::getByKey('system_name', 'Sistem Scoring Futsal Indonesia'),
            'default_half_duration' => Setting::getByKey('default_half_duration', '20'),
            'max_fouls_before_penalty' => Setting::getByKey('max_fouls_before_penalty', '5'),
            'max_timeout_per_period' => Setting::getByKey('max_timeout_per_period', '1'),
            'pdf_footer' => Setting::getByKey('pdf_footer', 'Sistem Futsal Scoring - Dokumen Resmi'),
            'timezone' => Setting::getByKey('timezone', 'Asia/Jakarta'),
            'copyright' => Setting::getByKey('copyright', 'Copyright 2026 @ Asosiasi Futsal Provinsi Jawa Barat'),
        ];

        return view('pages.admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'system_name' => 'required|string|max:100',
            'default_half_duration' => 'required|integer|min:5|max:45',
            'max_fouls_before_penalty' => 'required|integer|min:1|max:10',
            'max_timeout_per_period' => 'required|integer|min:1|max:5',
            'pdf_footer' => 'required|string|max:150',
            'timezone' => 'required|string|max:50',
            'copyright' => 'required|string|max:150',
        ]);

        $settings = $request->only([
            'system_name',
            'default_half_duration',
            'max_fouls_before_penalty',
            'max_timeout_per_period',
            'pdf_footer',
            'timezone',
            'copyright'
        ]);

        foreach ($settings as $key => $value) {
            Setting::setByKey($key, $value);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Mengubah pengaturan sistem',
            'subject_type' => 'Setting',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
