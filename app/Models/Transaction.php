<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions'; // ✅ matches your phpMyAdmin table name

    protected $fillable = [
        'transaction_id',
        'user_id',
        'amount',
        'status',
        'transaction_type',
    ];





    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
