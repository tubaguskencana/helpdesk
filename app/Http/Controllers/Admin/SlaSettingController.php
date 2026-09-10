<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaSettingController extends Controller
{
    public function index(): View
    {
        $settings = SlaSetting::all()->keyBy('priority');

        return view('admin.sla.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sla' => ['required', 'array'],
            'sla.*.first_response_hours' => ['required', 'integer', 'min:1'],
            'sla.*.resolution_hours' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['sla'] as $priority => $values) {
            SlaSetting::updateOrCreate(
                ['priority' => $priority],
                [
                    'first_response_hours' => $values['first_response_hours'],
                    'resolution_hours' => $values['resolution_hours'],
                ]
            );
        }

        return back()->with('success', 'SLA settings updated successfully.');
    }
}
