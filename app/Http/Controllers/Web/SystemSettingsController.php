<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::query()->first();
        $backups = Backup::query()->latest('completed_at')->limit(10)->get();
        $lastBackup = Backup::query()
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();

        return view('settings.index', compact('settings', 'backups', 'lastBackup'));
    }

    public function update()
    {
        $data = request()->validate([
            'default_payment_term_days' => ['required', 'integer', 'min:1'],
            'reminder_intervals' => ['required', 'string'],
            'invoice_template' => ['nullable', 'string'],
        ]);

        $rawIntervals = collect(explode(',', $data['reminder_intervals']))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values();

        $hasInvalidInterval = $rawIntervals->contains(
            fn (string $value): bool => ! preg_match('/^[1-9]\d*$/', $value)
        );

        if ($rawIntervals->isEmpty() || $hasInvalidInterval) {
            return back()
                ->withErrors(['reminder_intervals' => 'Please enter whole numbers separated by commas, for example: 15,30,45.'])
                ->withInput();
        }

        $intervals = $rawIntervals->map(fn (string $value): int => (int) $value)->all();

        SystemSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'default_payment_term_days' => $data['default_payment_term_days'],
                'reminder_intervals' => $intervals,
                'invoice_template' => $data['invoice_template'],
            ]
        );

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    public function runBackup(Request $request)
    {
        Artisan::call('backup:database', [
            '--manual' => true,
            '--triggered-by' => $request->user()->id,
        ]);

        $backup = Backup::query()->latest('id')->first();
        if ($backup) {
            AuditLog::query()->create([
                'user_id' => $request->user()->id,
                'action' => 'backup.manual_triggered',
                'entity_type' => 'Backup',
                'entity_id' => $backup->id,
                'old_values' => null,
                'new_values' => [
                    'filename' => $backup->filename,
                    'trigger_type' => $backup->trigger_type,
                    'status' => $backup->status,
                ],
                'ip_address' => $request->ip(),
            ]);
        }

        if (! $backup || $backup->status !== 'success') {
            $error = $backup?->error_message ?? trim(Artisan::output()) ?: 'Backup failed.';
            return back()->withErrors(['backup' => $error]);
        }

        return back()->with('success', 'Backup completed: ' . $backup->filename);
    }

    public function downloadBackup(Request $request, Backup $backup)
    {
        AuditLog::query()->create([
            'user_id' => $request->user()->id,
            'action' => 'backup.downloaded',
            'entity_type' => 'Backup',
            'entity_id' => $backup->id,
            'old_values' => null,
            'new_values' => [
                'filename' => $backup->filename,
                'status' => $backup->status,
            ],
            'ip_address' => $request->ip(),
        ]);

        return Storage::disk('local')->download($backup->file_path, $backup->filename);
    }
}
