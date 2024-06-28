<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\VcardController;
use App\Http\Controllers\api\DefaultCategoryController;
use App\Http\Controllers\api\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('vcards', [VcardController::class, 'store']);
Route::middleware('auth:api')->group(function () {
    
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users/me', [UserController::class, 'show_me']);

    //Admins
    Route::post('admins', [AdminController::class,'store'])->middleware('can:create,App\Models\Admin');
    Route::put('admins/{admin}', [AdminController::class,'update'])->middleware('can:update,admin');
    Route::delete('admins/{admin}', [AdminController::class,'destroy'])->middleware('can:delete,admin');
    Route::patch('admins/{admin}/password', [AdminController::class,'update_password'])->middleware('can:updatePassword,admin');

    //Users
    Route::get('users', [UserController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('can:view,user');
    Route::get('distributionOfUsers', [UserController::class, 'getDistributionOfUsers'])->middleware('can:getDistributionOfUsers,App\Models\User');
    
    //Vcards
    Route::get('vcards', [VcardController::class, 'index'])->middleware('can:viewAny,App\Models\Vcard');
    Route::get('vcards/{vcard}', [VcardController::class, 'show'])->middleware('can:view,vcard');
    Route::put('vcards/{vcard}', [VcardController::class, 'update'])->middleware('can:update,vcard');
    Route::patch('vcards/{vcard}/blocked', [VCardController::class, 'updateBlocked'])->middleware('can:updateBlocked,vcard');
    Route::patch('vcards/{vcard}/profile', [VCardController::class, 'updateProfile'])->middleware('can:updateProfile,vcard');
    Route::get('vcards/{vcard}/transactions', [VCardController::class, 'getTransactionsOfVcard'])->middleware('can:getTransactions,vcard');
    Route::delete('vcards/{vcard}', [VCardController::class,'destroy'])->middleware('can:delete,vcard');
    Route::patch('vcards/{vcard}/password', [VCardController::class, 'update_password'])->middleware('can:updatePassword,vcard');
    Route::patch('vcards/{vcard}/confirmationCode', [VCardController::class, 'update_confirmation_code'])->middleware('can:updateConfirmationCode,vcard');
    Route::get('vcards/{vcard}/categories', [VCardController::class, 'getCategoryOfVcard'])->middleware('can:getCategories,vcard');
    Route::get('vcardsActive', [VCardController::class, 'getActiveVcards'])->middleware('can:getActiveVcards,App\Models\Vcard');

    Route::get('vcards/{vcard}/transactionsCategories', [VcardController::class, 'getCategoriesOfTransactions'])->middleware('can:getCategoriesOfTransactions,vcard');
    Route::get('vcards/{vcard}/transactionsPaymentTypes', [VcardController::class, 'getPaymentTypesOfTransactionsVcard'])->middleware('can:getPaymentTypesOfTransactionsVcard,vcard');

    //Transactions

    Route::get('transactions', [TransactionController::class, 'index'])->middleware('can:viewAny,App\Models\Transaction');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->middleware('can:view,transaction');
    Route::put('transactions/{transaction}', [TransactionController::class,'update'])->middleware('can:update,transaction');
    Route::post('transactions', [TransactionController::class, 'store'])->middleware('can:create,App\Models\Transaction');
    Route::get('paymentTypesOftransactions', [TransactionController::class, 'getPaymentTypesOfTransactions'])->middleware('can:getPaymentTypesOfTransactions,App\Models\Transaction');
    Route::get('transactionsValid', [TransactionController::class, 'getTransactionsNotDeleted'])->middleware('can:getTransactionsNotDeleted,App\Models\Transaction');
    Route::get('transactionsPerMonth', [TransactionController::class, 'getTransactionsPerMonth'])->middleware('can:getTransactionsPerMonth,App\Models\Transaction');
    Route::get('transactionsPerType', [TransactionController::class, 'getTransactionsPerType'])->middleware('can:getTransactionsPerType,App\Models\Transaction');
    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->middleware('can:delete,transaction');
    Route::get('/transactions/{transaction}/pdf', [TransactionController::class, 'generatePDF'])->middleware('can:generatePDF,transaction');
    
    //Categorias

    Route::get('defaultCategories', [DefaultCategoryController::class, 'index'])->middleware('can:viewAny,App\Models\DefaultCategory');
    Route::get('defaultCategories/{defaultCategory}', [DefaultCategoryController::class, 'show'])->middleware('can:view,defaultCategory');
    Route::post('defaultCategories', [DefaultCategoryController::class, 'store'])->middleware('can:create,App\Models\DefaultCategory');
    Route::put('defaultCategories/{defaultCategory}', [DefaultCategoryController::class, 'update'])->middleware('can:update,defaultCategory');
    Route::delete('defaultCategories/{defaultCategory}', [DefaultCategoryController::class, 'delete'])->middleware('can:delete,defaultCategory');
    
    Route::get('categories', [CategoryController::class, 'index'])->middleware('can:viewAny,App\Models\Category');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->middleware('can:view,category');
    Route::post('categories', [CategoryController::class, 'store'])->middleware('can:create,App\Models\Category');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('can:update,category');
    Route::delete('categories/{category}', [CategoryController::class, 'delete'])->middleware('can:delete,category');

});










