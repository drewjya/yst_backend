<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasRole('Super Admin');
    }
}
