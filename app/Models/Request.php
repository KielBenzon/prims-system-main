<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $table = 'trequests';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'document_type',
        'requested_by',
        'approved_by',
        'status',
        'is_paid',
        'notes',
        'file',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }

    public function request_approved()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'request_id', 'id');
    }

    public function certificate_detail()
    {
        return $this->hasOne(CertificateDetail::class, 'certificate_type', 'document_type');
    }

    public function certificate_type()
    {
        return $this->belongsTo(CertificateType::class, 'document_type', 'certificate_type');
    }
}