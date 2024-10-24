<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// routes/api.php

use App\Http\Controllers\CertificateController;

Route::post('/certificate-holders', [CertificateController::class, 'addCertificateHolder']);
Route::post('/certificates', [CertificateController::class, 'addCertificate']);
Route::get('/certificates/{student_id}', [CertificateController::class, 'getCertificates']);

// Verify a certificate
Route::get('/certificates/verify/{certificate_id}', [CertificateController::class, 'verifyCertificate']);

