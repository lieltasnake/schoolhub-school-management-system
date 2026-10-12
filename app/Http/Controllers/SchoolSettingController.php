<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolSettingRequest;
use App\Models\SchoolSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SchoolSettingController extends Controller
{
    public function edit(): View
    {
        $this->authorize('viewAny', SchoolSetting::class);

        return view('settings.school', ['settings' => SchoolSetting::current()]);
    }

    public function update(SchoolSettingRequest $request): RedirectResponse
    {
        $settings = SchoolSetting::current() ?? new SchoolSetting;
        $settings->id = SchoolSetting::SINGLETON_ID;
        $settings->fill($request->validated());
        $settings->save();

        return redirect()->route('settings.school.edit')->with('success', 'School settings saved successfully.');
    }
}
