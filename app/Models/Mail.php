<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    protected $table = 'tmail';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'sender',
        'recipient',
        'subject',
        'priority',
        'status',
        'date',
    ];
}
