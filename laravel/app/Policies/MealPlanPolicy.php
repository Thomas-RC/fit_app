<?php

namespace App\Policies;

use App\Models\MealPlan;
use App\Models\User;

class MealPlanPolicy
{
    /**
     * Determine if the user can view the meal plan.
     */
    public function view(User $user, MealPlan $mealPlan): bool
    {
        return $user->id === $mealPlan->user_id;
    }

    /**
     * Determine if the user can update the meal plan.
     */
    public function update(User $user, MealPlan $mealPlan): bool
    {
        return $user->id === $mealPlan->user_id;
    }

    /**
     * Determine if the user can delete the meal plan.
     */
    public function delete(User $user, MealPlan $mealPlan): bool
    {
        return $user->id === $mealPlan->user_id;
    }
}
