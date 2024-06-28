<?php

namespace App\Http\Controllers\api;

use App\Models\Vcard;
use App\Models\Category;
use App\Models\Transaction;
use PDF;
use App\Http\Resources\TransactionResource;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;


class TransactionController extends Controller {

    public function index(Request $request) 
    {
        $transactionsQuery = Transaction::query();

        $transactionsQuery->whereNull('custom_options');

        $type = $request->type;
        $payment = $request->payment;
        $order = $request->order;

        if ($type != null)
            $transactionsQuery->where('type', $type);

        if ($payment != null)   
            $transactionsQuery->where('payment_type', $payment);

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

    public function show(Transaction $transaction) {
        return new TransactionResource($transaction);
    }
    
    public function getPaymentTypesOfTransactions (Request $request){

        $paymentCount = Transaction::select('payment_type', \DB::raw('COUNT(*) as count'))
            ->whereNull('custom_options')
            ->groupBy('payment_type')
            ->get();
    
        return response()->json($paymentCount);
    }

    public function generatePDF(Transaction $transaction)
    {
        if ($transaction->payment_type == 'MB')
            $transaction->payment_type = 'Multibanco';
        
        $pdf = PDF::loadView('pdf.transaction', ['transaction' => $transaction]);
        return $pdf->download('transaction_' . $transaction->id . '.pdf');
    }

    public function getTransactionsNotDeleted(Request $request) {
        $transactions = Transaction::whereNull('deleted_at')->whereNull('custom_options')->count();

        return response()->json($transactions);
    }

    public function getTransactionsPerType(Request $request) {
        $transactionsCountType = Transaction::select('type', \DB::raw('COUNT(*) as count'))
            ->whereNull('custom_options')
            ->groupBy('type')
            ->get();

        return response()->json($transactionsCountType);
    }

    

    public function getTransactionsPerMonth(Request $request) {
        $year = $request->input('year', date('Y'));
    
        $transactions = Transaction::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as transaction_count')
            ->whereNull('custom_options')
            ->whereYear('created_at', $year)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
            ->get();

        $transactions = $transactions->map(function ($transaction) {
            $transaction->year = substr($transaction->year, -2);
            return $transaction;
        });
    
        return response()->json($transactions);
    }
    

    public function store(StoreTransactionRequest $request) {
        $validatedRequest = $request->validated();

        $vcard = Vcard::findOrFail($validatedRequest['vcard']);

        if($vcard->blocked == 1) {
            return response()->json(['error' => "The Vcard is blocked can`t do transfers"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($validatedRequest['custom_options'] != null){

            if ($validatedRequest['vcard'] == $validatedRequest['payment_reference']){
                return response()->json(['error' => "You can't request money from yourself"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $requestTransaction = new Transaction([
                'vcard' => $validatedRequest['payment_reference'],
                'value' => $validatedRequest['value'],
                'type' => 'D',
                'payment_reference' => $validatedRequest['vcard'],
                'old_balance' => 0,
                'date' => now()->toDateString(),
                'datetime' => now(),
                'new_balance' => 0,
                'pair_vcard' => $validatedRequest['vcard'],
                'payment_type' => $validatedRequest['payment_type'],
                'custom_options' => $validatedRequest['custom_options'],
            ]);
            $requestTransaction->save();

            return new TransactionResource($requestTransaction);
        }

        $balancevard = $vcard->balance;

        if((($balancevard - $validatedRequest['value']) < 0) && $validatedRequest['type'] == 'D') {
            return response()->json(['error' => "The Vcard doesnt have enough money to complete the transaction"], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validatedRequest['value'] = round($validatedRequest['value'], 2);

        $validatedRequest['old_balance'] = $vcard->balance;
        
        if($validatedRequest['payment_type'] == 'VCARD') {
            $vcardReceiver = Vcard::findOrFail($validatedRequest['payment_reference']);
            $validatedRequest['pair_vcard'] = $vcardReceiver->phone_number;

            if($vcardReceiver->blocked == 1) {
                return response()->json(['error' => "The Vcard Receiver is blocked can`t do transfers"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if($validatedRequest['vcard'] == $validatedRequest['payment_reference']) {
                return response()->json(['error' => "You can't transfer money to yourself"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $createdTransaction = $this->createAdditionalTransaction($vcardReceiver, $validatedRequest);

            if($validatedRequest['type'] == 'D') {
                $vcardReceiver->balance += $validatedRequest['value'];
                $validatedRequest['new_balance'] = $vcard->balance -= $validatedRequest['value'];
                $vcard->save();
                $vcardReceiver->save();
    
            } else{
                $vcardReceiver->balance -= $validatedRequest['value'];
                $validatedRequest['new_balance'] = $vcard->balance;
                $vcard->save();
                $vcardReceiver->save();
            }

            
        } else {
            $paymentServiceUrl = 'https://dad-202324-payments-api.vercel.app';
            $paymentServiceEndpoint = '/api/'.($validatedRequest['type'] == 'C' ? 'debit' : 'credit');
            $paymentServiceUrl = $paymentServiceUrl.$paymentServiceEndpoint;

            $paymentServicePayload = [
                'type' => $validatedRequest['payment_type'],
                'reference' => $validatedRequest['payment_reference'],
                'value' => (float)$validatedRequest['value'],
            ];

            $paymentServiceResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($paymentServiceUrl, $paymentServicePayload);

            if($paymentServiceResponse->successful()) {
                if($validatedRequest['type'] == 'D') {
                    $validatedRequest['new_balance'] = $vcard->balance -= $validatedRequest['value'];
                    $vcard->save();
                } else {
                    $validatedRequest['new_balance'] = $vcard->balance += $validatedRequest['value'];
                    $vcard->save();
                }

            } else {
                return response()->json(['error' =>'Payment Reference doesn`t exist for the Payment Type'], $paymentServiceResponse->status());
            }
        }

        $validatedRequest['date'] = now()->toDateString();
        $validatedRequest['datetime'] = now();

        $newTransaction = $vcard->transactions()->create($validatedRequest);

        if($validatedRequest['payment_type'] == 'VCARD') {
            $createdTransaction['pair_transaction'] = $newTransaction->id;
            $newTransaction['pair_transaction'] = $createdTransaction->id;
            $createdTransaction->save();
            $newTransaction->save();
        }

        return new TransactionResource($newTransaction);
    }

    private function createAdditionalTransaction(Vcard $vcardReceiver, array $validatedRequest) {
        $additionalTransaction = new Transaction([
            'vcard' => $validatedRequest['payment_reference'],
            'value' => $validatedRequest['value'],
            'type' => $validatedRequest['type'] == 'C' ? 'D' : 'C',
            'payment_reference' => $validatedRequest['vcard'],
            'old_balance' => $vcardReceiver->balance,
            'date' => now()->toDateString(),
            'datetime' => now(),
            'new_balance' => $validatedRequest['type'] == 'C' ? $vcardReceiver->balance - $validatedRequest['value'] : $vcardReceiver->balance + $validatedRequest['value'],
            'pair_vcard' => $validatedRequest['vcard'],
            'payment_type' => $validatedRequest['payment_type'],
        ]);
        $additionalTransaction->save();

        return $additionalTransaction;
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction) {
        $transaction->update($request->validated());
        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction) {

        if ($transaction->custom_options != null){
            $transaction->forceDelete();
            return new TransactionResource($transaction);
        }

        // pode ser soft deleted se vcard soft deleted
        if($transaction->vcardOfTransaction->trashed()) {
            $transaction->delete();
            return new TransactionResource($transaction);
        }

        return response()->json(['error' => "Can't delete tre transaction - Vcard exists"], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}