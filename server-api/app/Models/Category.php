<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "categories";
    public $timestamps = false;
    protected $fillable = ['id', 'vcard', 'type', 'name', 'custom_options', 'custom_data', 'deleted_at'];

    public function getTypeOfCategoryAttribute(){    
        return $this->type == 'C' ? 'Credit' : 'Debit';
    }
    
    public function vcardOfCategory(){
        return $this->belongsTo(Vcard::class, 'vcard');
    }

    public function transactions(){
        return $this->hasMany(Transaction::class, 'category_id');
    }
    
}
