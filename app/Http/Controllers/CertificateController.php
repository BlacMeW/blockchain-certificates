<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateHolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CertificateController extends Controller
{
    // Function to add a new certificate holder
    public function addCertificateHolder(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'student_id' => 'required|unique:certificate_holders',
            'course_name' => 'required',
            'date_of_issue' => 'required|date',
        ]);

        $holder = CertificateHolder::create([
            'name' => $request->name,
            'student_id' => $request->student_id,
            'course_name' => $request->course_name,
            'date_of_issue' => $request->date_of_issue,
        ]);

        return response()->json($holder, 201);
    }

    // Function to calculate hash
    private function calculateHash($previousHash, $holderId, $courseName, $grade, $timestamp)
    {
        return hash('sha256', $previousHash . $holderId . $courseName . $grade . $timestamp);
    }

    // Function to get previous hash
    private function getPreviousHash()
    {
        $lastCertificate = Certificate::orderBy('id', 'desc')->first();
        return $lastCertificate ? $lastCertificate->hash : '0000000000000000'; // Genesis block if no previous
    }

    // Function to add a new certificate
    public function addCertificate(Request $request)
    {
        $request->validate([
            'holder_id' => 'required|exists:certificate_holders,id',
            'course_name' => 'required',
            'grade' => 'required',
        ]);

        $previousHash = $this->getPreviousHash();
        $timestamp = now();
        $calculatedHash = $this->calculateHash($previousHash, $request->holder_id, $request->course_name, $request->grade, $timestamp->format('Y-m-d H:i:s'));

        $certificate = Certificate::create([
            'holder_id' => $request->holder_id,
            'course_name' => $request->course_name,
            'grade' => $request->grade,
            'timestamp' => $timestamp,
            'previous_hash' => $previousHash,
            'hash' => $calculatedHash,
        ]);

        return response()->json($certificate, 201);
    }

    // Function to get all certificates by student ID
    public function getCertificates($student_id)
    {
        $holder = CertificateHolder::where('student_id', $student_id)->firstOrFail();
        $certificates = $holder->certificates()->get();

        return response()->json($certificates);
    }

   

public function verifyCertificate($certificate_id)
{
    // Find the certificate by its ID
    $certificate = Certificate::find($certificate_id);

    if (!$certificate) {
        return response()->json(['message' => 'Certificate not found'], 404);
    }

    // Convert timestamp to Carbon instance if it's stored as a string
    $timestamp = Carbon::parse($certificate->timestamp);

    // Recalculate hash based on certificate data
    $calculatedHash = $this->calculateHash(
        $certificate->previous_hash,
        $certificate->holder_id,
        $certificate->course_name,
        $certificate->grade,
        $timestamp->format('Y-m-d H:i:s') // Ensure timestamp is formatted correctly
    );

    // Compare the calculated hash with the stored hash
    if ($calculatedHash !== $certificate->hash) {
        return response()->json(['message' => 'Certificate has been tampered with'], 400);
    }

    // Check if the previous hash matches the previous certificate's hash (if it exists)
    if ($certificate->previous_hash === '0000000000000000') {
        return response()->json(['message' => 'Certificate is valid and verified'], 200);
    }

    $previousCertificate = Certificate::where('hash', $certificate->previous_hash)->first();

    if ($previousCertificate) {
        return response()->json(['message' => 'Certificate is valid and verified'], 200);
    }

    return response()->json(['message' => 'Invalid certificate chain'], 400);
}

}
