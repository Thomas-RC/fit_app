<?php

namespace App\Policies;

use App\Models\CustomDish;
use App\Models\User;

class CustomDishPolicy
{
    /**
     * Determine if the user can view the custom dish.
     */
    public function view(User $user, CustomDish $customDish): bool
    {
        return $user->id === $customDish->user_id;
    }

    /**
     * Determine if the user can update the custom dish.
     */
    public function update(User $user, CustomDish $customDish): bool
    {
        return $user->id === $customDish->user_id;
    }

    /**
     * Determine if the user can delete the custom dish.
     */
    public function delete(User $user, CustomDish $customDish): bool
    {
        return $user->id === $customDish->user_id;
    }
}
