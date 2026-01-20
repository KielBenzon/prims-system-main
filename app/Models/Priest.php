<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Priest extends Model
{
    protected $table = 'tpriests';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'title',
        'date_of_birth',
        'phone_number',
        'email_address',
        'ordination_date',
        'image'
    ];
}