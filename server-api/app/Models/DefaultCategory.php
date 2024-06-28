<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefaultCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;
    protected $fillable = ['id', 'type', 'name', 'custom_options', 'custom_data', 'deleted_at'];

    public function getTypeOfCategoryAttribute(){    
        return $this->type == 'C' ? 'Credit' : 'Debit';
    }
}
