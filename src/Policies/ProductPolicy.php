<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEditor();
    }

    public function create(User $user): bool
    {
        return $user->isEditor();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isEditor();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isEditor();
    }
}
