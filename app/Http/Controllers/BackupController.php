<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    /**
     * Download the current SQLite database file as a backup.
     */
    public function download(): BinaryFileResponse
    {
        $this->authorize('viewAny', SchoolSetting::class);

        $dbPath = database_path('database.sqlite');

        abort_unless(file_exists($dbPath), 404, 'Database file not found.');

        $filename = 'schoolhub-backup-' . now()->format('Y-m-d_His') . '.sqlite';

        return response()->download($dbPath, $filename, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Restore the database from an uploaded SQLite backup file.
     */
    public function restore(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SchoolSetting::class);

        $request->validate([
            'backup_file' => ['required', 'file', 'max:102400'], // max 100 MB
        ]);

        $file = $request->file('backup_file');

        // Basic safety check: SQLite files start with the magic header
        $handle = fopen($file->getRealPath(), 'rb');
        $header = fread($handle, 16);
        fclose($handle);

        if (strpos($header, 'SQLite format 3') !== 0) {
            return redirect()
                ->route('settings.school.edit', ['tab' => 'backup'])
                ->with('error', 'Invalid file. Please upload a valid SQLite backup file.');
        }

        $dbPath = database_path('database.sqlite');

        // Create a safety snapshot before overwriting
        $snapshotPath = database_path('database.sqlite.bak');
        @copy($dbPath, $snapshotPath);

        // Replace the database
        $file->move(database_path(), 'database.sqlite');

        return redirect()
            ->route('settings.school.edit', ['tab' => 'backup'])
            ->with('success', 'Database restored successfully. A safety snapshot was saved as database.sqlite.bak.');
    }
}
