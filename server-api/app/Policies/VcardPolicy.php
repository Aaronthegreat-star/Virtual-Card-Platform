<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vcard;
use Illuminate\Auth\Access\Response;

class VcardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->user_type == 'A';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vcard $vcard): bool
    {
        return $user->user_type == 'A' || $user->id == $vcard->phone_number;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vcard $vcard): bool
    {
        return $user->user_type == 'A' || $user->id == $vcard->phone_number;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vcard $vcard): bool
    {
        return $user->user_type == 'A' || $user->id == $vcard->phone_number;
    }

    public function updatePassword(User $user, Vcard $vcard): bool
    {
        return $user->id == $vcard->phone_number;
    }

    public function updateConfirmationCode(User $user, Vcard $vcard): bool
    {
        return $user->id == $vcard->phone_number;
    }
    
    public function updateBlocked(User $user, Vcard $vcard): bool
    {
        return $user->user_type == 'A';
    }

    public function updateProfile(User $user, Vcard $vcard): bool
    {
        return $user->user_type == 'A' || $user->id == $vcard->phone_number;
    }

    public function getTransactions(User $user, Vcard $vcard): bool
    {
        return $user->user_type == 'A' || $user->id == $vcard->phone_number;
    }

    public function getCategories(User $user, Vcard $vcard): bool
    {
        return $user->user_type == 'A' || $user->id == $vcard->phone_number;
    }

    public function getActiveVcards(User $user): bool
    {
        return $user->user_type == 'A';
    }

    public function getCategoriesOfTransactions (User $user, Vcard $vcard): bool
    {
        return $user->id == $vcard->phone_number;
    }

    public function getPaymentTypesOfTransactionsVcard (User $user, Vcard $vcard): bool
    {
        return $user->id == $vcard->phone_number;
    }
}
