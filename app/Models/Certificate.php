<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'holder_id',
        'course_name',
        'grade',
        'timestamp',
        'previous_hash',
        'hash'
    ];

    public function holder()
    {
        return $this->belongsTo(CertificateHolder::class, 'holder_id');
    }
}
