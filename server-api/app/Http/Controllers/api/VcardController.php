<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Vcard;
use App\Models\DefaultCategory;
use App\Models\Category;
use App\Models\Transaction;
use App\Http\Resources\VcardResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreVcardRequest;
use App\Http\Requests\DeleteVcardRequest;
use App\Http\Requests\UpdateVcardProfileRequest;
use App\Http\Requests\UpdateVcardRequest;
use App\Http\Requests\UpdateBlockVcardRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserConfirmationCodeRequest;
use App\Http\Requests\UpdateMaxDebitVcardRequest;
use Illuminate\Http\Response;
use App\Services\Base64Services;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class VcardController extends Controller
{

    private function storeBase64AsFile(Vcard $vcard, String $base64String)
    {
        $targetDir = storage_path('app/public/fotos');
        $newfilename = $vcard->phone_number . "_" . rand(1000,9999);
        $base64Service = new Base64Services();
        return $base64Service->saveFile($base64String, $targetDir, $newfilename);
    }

    public function index(Request $request)
    {   
        $vcardsQuery = Vcard::query();

        $phone_number = $request->owner;
        $blocked = $request->blocked;
        $order = $request->order;

        if ($phone_number != null)
            $vcardsQuery->where('phone_number', 'LIKE', $phone_number);
        
        
        if ($blocked != null)
            $vcardsQuery->where('blocked' , $blocked);

        if ($order != null){
            $vcardsQuery->orderBy('created_at', $order);
        }
        

        return VcardResource::collection($vcardsQuery->paginate(15));
    }

    public function show (Vcard $vcard){
        return new VCardResource($vcard);
    }

    public function store(StoreVcardRequest $request){

        $validatedRequest = $request->validated();
        $validatedRequest['blocked'] = 0;
        $validatedRequest['max_debit'] = 2500;
        $validatedRequest['balance'] = 0;

        $newVcard = Vcard::create($validatedRequest); 

        //associar categorias ao vcard 

        $newVcard->phone_number=$validatedRequest['phone_number'];
        // ate dar fix - resource aparece com number a 0

        $defaultCategories = DefaultCategory::all();

        foreach ($defaultCategories as $defaultCategory) {
            // criar entrada na tabela category
            $category = new Category([
                'vcard' => $newVcard->phone_number,
                'type' => $defaultCategory->type,
                'name' => $defaultCategory->name
            ]);
            $category->save();
        }
        
        return new VcardResource($newVcard);
    }

    public function update(UpdateVcardRequest $request, Vcard $vcard)
    {
        $vcard->update($request->validated());
        return new VcardResource($vcard);
    }

    public function updateMaxDebit(UpdateMaxDebitVcardRequest $request, Vcard $vcard)
    {
        $vcard->update($request->validated());
        return new VcardResource($vcard);
    }
    
    public function updateBlocked (UpdateBlockVcardRequest $request, Vcard $vcard){
        $vcard->blocked = $request->blocked;
        $vcard->save();
        return new VcardResource($vcard);
    }

    public function updateProfile (UpdateVcardProfileRequest $request, Vcard $vcard){

        $dataToSave = $request->validated();

        $base64ImagePhoto = array_key_exists("base64ImagePhoto", $dataToSave) ?
            $dataToSave["base64ImagePhoto"] : ($dataToSave["base64ImagePhoto"] ?? null);
        $deletePhotoOnServer = array_key_exists("deletePhotoOnServer", $dataToSave) && $dataToSave["deletePhotoOnServer"];
        unset($dataToSave["base64ImagePhoto"]);
        unset($dataToSave["deletePhotoOnServer"]);

        $vcard->name = $dataToSave['name'];
        $vcard->email = $request['email'];

        if ($vcard->photo_url && ($deletePhotoOnServer || $base64ImagePhoto)) {
            if (Storage::exists('public/fotos/' . $vcard->photo_url)) {
                Storage::delete('public/fotos/' . $vcard->photo_url);
            }
            $vcard->photo_url = null;
        }

        if ($base64ImagePhoto) {
            $vcard->photo_url = $this->storeBase64AsFile($vcard, $base64ImagePhoto);
        }

        $vcard->save();
        return new VcardResource($vcard);
    }


    public function destroy (Request $request, Vcard $vcard){
         
        $validator = Validator::make($request->json()->all()['body'], [
            'password' => ['required',function ($attribute, $value, $fail) use ($vcard){
                if (!$this->validateCurrentPassword($value, $vcard)) {
                    $fail('Invalid Password.');
                }
            }],
            'confirmation_code' => ['required',function ($attribute, $value, $fail) use ($vcard){
                if (!$this->validateCurrentConfirmationCode($value, $vcard)) {
                    $fail('Invalid Confirmation Code.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($vcard->balance != 0)
            return response()->json(['error' => "Can't delete the Vcard - Balance different than 0"], Response::HTTP_UNPROCESSABLE_ENTITY); // nao e possivel eliminar 

        if (Storage::exists('public/fotos/' . $vcard->photo_url)) {
            Storage::delete('public/fotos/' . $vcard->photo_url);
        }

        // soft delete categorias e transactions


        // soft delete se tiver transacoes senao forceDelete

        $countTransactions = count($vcard->transactions);

        if (count($vcard->categories) != 0){   
            
            if ($countTransactions == 0){
                foreach ($vcard->categories as $category) {
                    $category->forceDelete();
                }
            }else{
                foreach ($vcard->categories as $category) {
                    $category->delete();
                }
            }
        }

        if ($countTransactions){
            $vcard->delete();
            foreach ($vcard->transactions as $transaction) {
                $transaction->delete();
            }
        }else{
            $vcard->forceDelete();
        }

        // se for o proprio owner do vcard limpar token login
        if ($request['body']['sameUser']){
            // invalidate token -> logout
            $accessToken = $request->user()->token();
            $token = $request->user()->tokens->find($accessToken);
            $token->revoke();
            $token->delete();
        }
            
        return new VcardResource($vcard);
    }

    protected function validateCurrentConfirmationCode($currentConfirmationCode, Vcard $vcard): bool
    {
        return Hash::check($currentConfirmationCode, $vcard->confirmation_code);
    }

    protected function validateCurrentPassword($currentPassword, Vcard $vcard): bool
    {
        return Hash::check($currentPassword, $vcard->password);
    }

    public function getTransactionsOfVcard(Vcard $vcard, Request $request)
    {

        $transactionsQuery = $vcard->transactions();

        $requested = $request->requested;

        if (strcmp($requested,'req') == 0){
            $transactionsQuery->whereNotNull('custom_options');
        }elseif(strcmp($requested,'nreq') == 0){
            $transactionsQuery->whereNull('custom_options');
        }else{
            $transactionsQuery->orderBy('custom_options', 'desc');
        }

        $type = $request->type;
        $payment = $request->payment;
        $category = $request->category;
        $order = $request->order;

        if ($type != null)
            $transactionsQuery->where('type', $type);

        if ($payment != null)   
            $transactionsQuery->where('payment_type', $payment);

        if (strcmp($category,'none') == 0)
            $transactionsQuery->whereNull('category_id');
        else if ($category != null)   
            $transactionsQuery->where('category_id', $category);

        switch($order){
            case 'pasc':
                $transactionsQuery->orderBy('value', 'asc');
                break;
            case 'pdesc':
                $transactionsQuery->orderBy('value', 'desc');
                break;
            case 'asc';
                $transactionsQuery->orderBy('created_at', 'asc');
                break;
            case 'desc':
                $transactionsQuery->orderBy('created_at', 'desc');
                break;
            default:
        }

        return TransactionResource::collection($transactionsQuery->orderBy('created_at', 'desc')->paginate(15));
    }

    public function getCategoryOfVcard(Vcard $vcard, Request $request) {

        if ($request->paginate == '0')
            return CategoryResource::collection($vcard->categories()->orderBy('name', 'asc')->get());
    
        $pagedCategories = $vcard->categories()->orderBy('name', 'asc')->paginate(10);

        return CategoryResource::collection($pagedCategories);
    }

    public function getActiveVcards(Request $request) {
        $activeVcards = VCard::where('blocked', 0)->whereNull('deleted_at')->select('balance')->get();
        
    return response()->json($activeVcards);
    }

    public function update_password (UpdateUserPasswordRequest $request, Vcard $vcard)
    {
        $vcard->password = bcrypt($request->validated()['password']);
        $vcard->save();
        return new VcardResource($vcard);
    }

    public function update_confirmation_code (UpdateUserConfirmationCodeRequest $request, Vcard $vcard)
    {
        $vcard->confirmation_code = bcrypt($request->validated()['confirmation_code']);
        $vcard->save();
        return new VcardResource($vcard);
    }

    public function getCategoriesOfTransactions(Request $request, Vcard $vcard) {
        
        if (!$vcard) {
            return response()->json(['error' => 'Vcard not found'], Response::HTTP_NOT_FOUND);
        }
    
        $categoryCounts = Transaction::select('categories.name', \DB::raw('COUNT(*) as count'))
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.vcard', $vcard->phone_number)
            ->groupBy('categories.name')
            ->get();
    
        return response()->json($categoryCounts);
    }

    public function getPaymentTypesOfTransactionsVcard(Request $request, Vcard $vcard) {
        
        if (!$vcard) {
            return response()->json(['error' => 'Vcard not found'], Response::HTTP_NOT_FOUND);
        }
    
        $paymentCount = Transaction::select('payment_type', \DB::raw('COUNT(*) as count'))
            ->where('transactions.vcard', $vcard->phone_number)
            ->whereNull('custom_options')
            ->groupBy('payment_type')
            ->get();
    
        return response()->json($paymentCount);
    }
}
