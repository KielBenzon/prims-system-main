<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $table = 'tdonations';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
    'donor_name',
    'donor_email',
    'donor_phone',
    'amount',
    'donation_date',
    'note',
    'transaction_id',
    'proof',
    'status',
];
}