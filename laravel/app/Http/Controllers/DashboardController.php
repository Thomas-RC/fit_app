<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        return view('dashboard.index', [
            'fridgeItemsCount' => $user->fridgeItems()->count(),
            'mealPlansCount' => $user->mealPlans()->count(),
            'customDishesCount' => $user->customDishes()->count(),
            'dailyCalories' => $user->preferences?->daily_calories ?? 2000,
            'recentMealPlans' => $user->mealPlans()
                ->with('recipes')
                ->latest()
                ->take(3)
                ->get(),
            'expiringItems' => $user->fridgeItems()
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [now(), now()->addDays(3)])
                ->orderBy('expires_at')
                ->get(),
        ]);
    }
}
