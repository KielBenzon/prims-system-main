<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'tannouncements';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'title',
        'content',
        'assigned_priest'
    ];

    public function priest()
    {
        return $this->belongsTo(Priest::class, 'assigned_priest', 'id');
    }
}
