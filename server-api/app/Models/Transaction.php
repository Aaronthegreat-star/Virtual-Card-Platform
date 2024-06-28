<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $fillable = ['type','vcard','date','datetime','value','old_balance','new_balance','payment_reference','payment_type','pair_vcard','pair_transaction','category_id','description', 'custom_options'];

    public function getTypeOfTransactionAttribute(){
        return $this->type == 'C' ? 'Credit Transaction' : 'Debit Transaction';
    }

    public function vcardOfTransaction(){
        return $this->belongsTo(Vcard::class, 'vcard', 'phone_number')->withTrashed();
    }

    public function category(){
        return $this->belongsTo(Category::class, 'category_id')->withTrashed();
    }

    public function transactionPairVcard(){
        return $this->belongsTo(Vcard::class, 'pair_vcard', 'phone_number')->withTrashed();
    }

    public function pair_transactions(){
        return $this->hasOne(self::class,'pair_transaction');
    }
    
}
