<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEditor();
    }

    public function create(User $user): bool
    {
        return $user->isEditor();
    }

    public function update(User $user, Article $article): bool
    {
        return $user->isEditor();
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->isEditor();
    }
}
