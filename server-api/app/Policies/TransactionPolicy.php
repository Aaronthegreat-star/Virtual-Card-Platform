<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Vcard;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
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
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->user_type == 'A' || $user->id == $transaction->vcard;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user != null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id == $transaction->vcard;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->id == $transaction->vcard;
    }

    public function getPaymentTypesOfTransactions (User $user): bool 
    {
        return $user->user_type == 'A';
    }

    public function getTransactionsNotDeleted (User $user): bool 
    {
        return $user->user_type == 'A';
    }

    public function getTransactionsPerMonth (User $user): bool 
    {
        return $user->user_type == 'A';
    }

    public function getTransactionsPerType (User $user): bool 
    {
        return $user->user_type == 'A';
    }

    public function generatePDF (User $user, Transaction $transaction): bool 
    {
        return $user != null;
    }

}
