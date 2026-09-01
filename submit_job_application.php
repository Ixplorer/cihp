<?php
/**
 * CIHP Job & Consultant Roster Application Backend Handler
 * Centre for Integrated Health Programs (CIHP)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Only POST requests are accepted.'
    ]);
    exit();
}

$uploadDir = __DIR__ . '/uploads/cv/';
if (!file_exists($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$dataDir = __DIR__ . '/data/';
if (!file_exists($dataDir)) {
    @mkdir($dataDir, 0777, true);
}

// Generate unique Reference ID: CIHP-APP-2026-XXXXX or CIHP-STTA-2026-XXXXX
$appType         = filter_input(INPUT_POST, 'application_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'job';
$prefix          = ($appType === 'consultant_stta') ? 'CIHP-STTA-2026-' : 'CIHP-APP-2026-';
$randomNum       = sprintf("%05d", mt_rand(10000, 99999));
$referenceId     = $prefix . $randomNum;

$jobId           = filter_input(INPUT_POST, 'job_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$jobTitle        = filter_input(INPUT_POST, 'job_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'General Talent Pool';
$applicantName   = filter_input(INPUT_POST, 'applicant_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$email           = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '';
$phone           = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$location        = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'Nigeria';
$qualification   = filter_input(INPUT_POST, 'qualification', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$experienceYears = filter_input(INPUT_POST, 'experience_years', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$coverLetter     = filter_input(INPUT_POST, 'cover_letter', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

// Skill Tags for STTA / Consultant Roster
$skillTags       = isset($_POST['skill_tags']) ? (array)$_POST['skill_tags'] : [];

if (empty($applicantName) || empty($email) || empty($phone) || empty($qualification)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing mandatory applicant profile information.'
    ]);
    exit();
}

// Process CV File Upload
$cvFilename = '';
$cvPath = '';
$maxSizeBytes = 10 * 1024 * 1024; // 10MB
$allowedExtensions = ['pdf', 'doc', 'docx'];

if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
    $fileInfo = $_FILES['cv_file'];
    $fileName = $fileInfo['name'];
    $fileTmp  = $fileInfo['tmp_name'];
    $fileSize = $fileInfo['size'];
    $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileSize > $maxSizeBytes) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Uploaded CV file exceeds the maximum allowed 10MB limit.'
        ]);
        exit();
    }

    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid CV file format. Only PDF and Word documents are accepted.'
        ]);
        exit();
    }

    $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
    $targetPath = $uploadDir . $referenceId . '_' . $safeName;
    if (@move_uploaded_file($fileTmp, $targetPath)) {
        $cvFilename = $fileName;
        $cvPath = 'uploads/cv/' . $referenceId . '_' . $safeName;
    }
}

$newApplication = [
    'reference_id'     => $referenceId,
    'application_type' => $appType,
    'job_id'           => $jobId,
    'job_title'        => $jobTitle,
    'applicant_name'   => $applicantName,
    'email'            => $email,
    'phone'            => $phone,
    'location'         => $location,
    'qualification'    => $qualification,
    'experience_years' => $experienceYears,
    'skill_tags'       => $skillTags,
    'cover_letter'     => $coverLetter,
    'cv_filename'      => $cvFilename,
    'cv_path'          => $cvPath,
    'application_date' => date('Y-m-d H:i:s'),
    'status'           => 'Applied',
    'hr_notes'         => ($appType === 'consultant_stta') ? 'STTA Roster submission tagged with expertise.' : 'New application received via CIHP Careers Portal.'
];

// Append application to JSON database
$jsonFile = $dataDir . 'applications.json';
$existingApps = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
if (!is_array($existingApps)) { $existingApps = []; }
array_unshift($existingApps, $newApplication);
@file_put_contents($jsonFile, json_encode($existingApps, JSON_PRETTY_PRINT));

echo json_encode([
    'status'           => 'success',
    'message'          => 'Application submitted successfully.',
    'reference_id'     => $referenceId,
    'applicant_name'   => $applicantName,
    'job_title'        => $jobTitle,
    'email'            => $email,
    'application_date' => date('F j, Y - g:i A')
]);
