<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'tnotifications';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'user id',
        'type',
        'message',
        'read_at',
    ];
}