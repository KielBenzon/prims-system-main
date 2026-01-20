<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;
    
    protected $table = 'tdocuments';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'document_type',
        'full_name',
        'file',
        'uploaded_by',
    ];
}
