<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateType extends Model
{
    protected $table = 'tcertificate_types';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'certificate_type',
        'description',
        'amount',
    ];

    public function certificate_details()
    {
        return $this->hasMany(CertificateDetail::class, 'certificate_type', 'id');
    }

    public function request()
    {
        return $this->hasOne(Request::class, 'document_type', 'id');
    }
}
