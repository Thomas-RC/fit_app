<?php

namespace App\Policies;

use App\Models\FridgeItem;
use App\Models\User;

class FridgeItemPolicy
{
    /**
     * Determine if the user can view the fridge item.
     */
    public function view(User $user, FridgeItem $fridgeItem): bool
    {
        return $user->id === $fridgeItem->user_id;
    }

    /**
     * Determine if the user can update the fridge item.
     */
    public function update(User $user, FridgeItem $fridgeItem): bool
    {
        return $user->id === $fridgeItem->user_id;
    }

    /**
     * Determine if the user can delete the fridge item.
     */
    public function delete(User $user, FridgeItem $fridgeItem): bool
    {
        return $user->id === $fridgeItem->user_id;
    }
}
