<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user)
    {
        return $user->user_type == "A";
    }

    public function view(User $user, User $model)
    {
        return $user->user_type == "A" || $user->id == $model->id;
    }

    public function getDistributionOfUsers(User $user)
    {
        return $user->user_type == "A";
    }
}
