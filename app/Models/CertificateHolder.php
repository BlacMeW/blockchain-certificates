<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateHolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'student_id',
        'course_name',
        'date_of_issue'
    ];

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'holder_id');
    }
}
