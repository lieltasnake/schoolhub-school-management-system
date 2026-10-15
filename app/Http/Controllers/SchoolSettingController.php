<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolSettingRequest;
use App\Models\SchoolSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorize('viewAny', SchoolSetting::class);

        $tab = in_array($request->query('tab'), ['profile', 'notifications', 'backup'])
            ? $request->query('tab')
            : 'profile';

        return view('settings.school', [
            'settings' => SchoolSetting::current(),
            'tab'      => $tab,
        ]);
    }

    public function update(SchoolSettingRequest $request): RedirectResponse
    {
        $settings = SchoolSetting::current() ?? new SchoolSetting;
        $settings->id = SchoolSetting::SINGLETON_ID;

        $data = $request->safe()->except(['logo']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('school/logo', 'public');
            $data['logo_path'] = $path;
        }

        $settings->fill($data);
        $settings->save();

        $tab = $request->input('_tab', 'profile');
        $params = ($tab && $tab !== 'profile') ? ['tab' => $tab] : [];

        return redirect()
            ->route('settings.school.edit', $params)
            ->with('success', 'Settings saved successfully.');
    }
}
