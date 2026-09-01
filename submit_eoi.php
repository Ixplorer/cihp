<?php
/**
 * CIHP Expression of Interest (EOI) Submission Backend Handler
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
        'message' => 'Invalid request method. Only POST requests are accepted.'
    ]);
    exit();
}

// Ensure storage directory exists
$uploadDir = __DIR__ . '/uploads/eoi_submissions/';
if (!file_exists($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

// Generate unique Reference ID: CIHP-EOI-2026-XXXXX
$randomNum = sprintf("%05d", mt_rand(1000, 99999));
$referenceId = 'CIHP-EOI-2026-' . $randomNum;

// Retrieve POST fields
$companyName     = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$cacNumber       = filter_input(INPUT_POST, 'cac_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$yearEstablished = filter_input(INPUT_POST, 'year_established', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$companyAddress  = filter_input(INPUT_POST, 'company_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$officialEmail   = filter_input(INPUT_POST, 'official_email', FILTER_VALIDATE_EMAIL) ?? '';
$officialPhone   = filter_input(INPUT_POST, 'official_phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$contactPerson   = filter_input(INPUT_POST, 'contact_person', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$contactTitle    = filter_input(INPUT_POST, 'contact_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$contactPhone    = filter_input(INPUT_POST, 'contact_phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

$categoryGroupLife = isset($_POST['cat_group_life']) ? 'Group Life Insurance' : null;
$categoryBrokerage = isset($_POST['cat_brokerage'])  ? 'Insurance Brokerage (Comprehensive Physical Assets)' : null;
$categoriesSelected = array_values(array_filter([$categoryGroupLife, $categoryBrokerage]));

$tinNumber       = filter_input(INPUT_POST, 'tin_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$regulatoryLic   = filter_input(INPUT_POST, 'regulatory_license', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$bankName        = filter_input(INPUT_POST, 'bank_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$accountName     = filter_input(INPUT_POST, 'account_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$declarationName = filter_input(INPUT_POST, 'declaration_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

if (empty($companyName) || empty($officialEmail) || empty($cacNumber) || empty($categoriesSelected)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing mandatory company or category selection fields.'
    ]);
    exit();
}

// Process Uploaded Files
$uploadedFilesInfo = [];
$allowedExtensions = ['pdf', 'doc', 'docx', 'zip'];
$maxSizeBytes = 10 * 1024 * 1024; // 10MB limit per file

$appUploadFolder = $uploadDir . $referenceId . '/';
if (!file_exists($appUploadFolder)) {
    @mkdir($appUploadFolder, 0777, true);
}

if (!empty($_FILES)) {
    foreach ($_FILES as $fieldKey => $fileData) {
        if (is_array($fileData['name'])) {
            // Multiple files array
            foreach ($fileData['name'] as $idx => $fileName) {
                if ($fileData['error'][$idx] === UPLOAD_ERR_OK) {
                    $tmpName = $fileData['tmp_name'][$idx];
                    $size    = $fileData['size'][$idx];
                    $ext     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if ($size > $maxSizeBytes) {
                        continue; // Skip over-sized files
                    }

                    if (in_array($ext, $allowedExtensions)) {
                        $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
                        $targetPath = $appUploadFolder . $fieldKey . '_' . $idx . '_' . $safeName;
                        if (@move_uploaded_file($tmpName, $targetPath)) {
                            $uploadedFilesInfo[] = [
                                'field' => $fieldKey,
                                'original_name' => $fileName,
                                'saved_path' => $targetPath
                            ];
                        }
                    }
                }
            }
        } else {
            // Single file
            if ($fileData['error'] === UPLOAD_ERR_OK) {
                $fileName = $fileData['name'];
                $tmpName  = $fileData['tmp_name'];
                $size     = $fileData['size'];
                $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if ($size <= $maxSizeBytes && in_array($ext, $allowedExtensions)) {
                    $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
                    $targetPath = $appUploadFolder . $fieldKey . '_' . $safeName;
                    if (@move_uploaded_file($tmpName, $targetPath)) {
                        $uploadedFilesInfo[] = [
                            'field' => $fieldKey,
                            'original_name' => $fileName,
                            'saved_path' => $targetPath
                        ];
                    }
                }
            }
        }
    }
}

// Record submission entry as JSON log
$submissionData = [
    'reference_id'       => $referenceId,
    'submission_time'    => date('Y-m-d H:i:s T'),
    'company_name'       => $companyName,
    'cac_number'         => $cacNumber,
    'year_established'   => $yearEstablished,
    'company_address'    => $companyAddress,
    'official_email'     => $officialEmail,
    'official_phone'     => $officialPhone,
    'contact_person'     => $contactPerson,
    'contact_title'      => $contactTitle,
    'contact_phone'      => $contactPhone,
    'categories'         => $categoriesSelected,
    'tin_number'         => $tinNumber,
    'regulatory_license' => $regulatoryLic,
    'bank_name'          => $bankName,
    'account_name'       => $accountName,
    'declaration_by'     => $declarationName,
    'uploaded_files'     => $uploadedFilesInfo
];

$recordFile = $appUploadFolder . 'application_record.json';
@file_put_contents($recordFile, json_encode($submissionData, JSON_PRETTY_PRINT));

// Master log index
$masterLog = $uploadDir . 'master_submissions_index.json';
$existingLogs = file_exists($masterLog) ? json_decode(file_get_contents($masterLog), true) : [];
if (!is_array($existingLogs)) { $existingLogs = []; }
$existingLogs[] = [
    'reference_id' => $referenceId,
    'company_name' => $companyName,
    'official_email' => $officialEmail,
    'categories' => implode(', ', $categoriesSelected),
    'date' => date('Y-m-d H:i:s')
];
@file_put_contents($masterLog, json_encode($existingLogs, JSON_PRETTY_PRINT));

echo json_encode([
    'status' => 'success',
    'message' => 'EOI Pre-qualification Application submitted successfully.',
    'reference_id' => $referenceId,
    'company_name' => $companyName,
    'submission_date' => date('F j, Y - g:i A'),
    'categories' => $categoriesSelected,
    'official_email' => $officialEmail
]);
