<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePreferencesRequest;
use App\Models\UserPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PreferenceController extends Controller
{
    /**
     * Display the user's preferences form.
     */
    public function show(): View
    {
        $user = auth()->user();

        // Get existing preferences or create default
        $preferences = $user->preferences ?? new UserPreference([
            'diet_type' => 'omnivore',
            'daily_calories' => 2000,
            'allergies' => [],
            'exclude_ingredients' => [],
        ]);

        return view('preferences.index', compact('preferences'));
    }

    /**
     * Update the user's preferences.
     */
    public function update(UpdatePreferencesRequest $request): RedirectResponse
    {
        $user = auth()->user();

        // Update or create preferences
        $user->preferences()->updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return redirect()
            ->route('preferences.show')
            ->with('success', 'Preferencje zostały zaktualizowane!');
    }
}
