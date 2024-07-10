<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Set options for Dompdf
$options = new Options();
$options->set('defaultFont', 'Courier');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Enable remote file access

$dompdf = new Dompdf($options);

$uploadDir = 'uploads/';

// Function to delete a directory and its contents
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

// Delete the existing uploads directory if it exists
if (file_exists($uploadDir)) {
    deleteDirectory($uploadDir);
}

// Create a new uploads directory
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $site_n = htmlspecialchars($_POST['site_name']);
    $solucomp_t = htmlspecialchars($_POST['solucomp_tech']);
    $engineer_co = htmlspecialchars($_POST['engineer_contact']);
    $customer_engin = htmlspecialchars($_POST['customer_engineer_name']);

    $uploadDir = 'uploads/';
    $categories = ['POST_INSTALLATION_PICTURES', 'MATERIAL_PICTURES', 'PRE_INSTALLATION_PICTURES'];
    $uploadedFiles = [];

    foreach ($categories as $category) {
        if (!empty($_FILES[$category]['name'][0])) {
            $categoryDir = $uploadDir . $category . '/';
            if (!is_dir($categoryDir)) {
                mkdir($categoryDir, 0777, true);
            }
            foreach ($_FILES[$category]['tmp_name'] as $key => $tmpName) {
                $filePath = $categoryDir . basename($_FILES[$category]['name'][$key]);
                if (move_uploaded_file($tmpName, $filePath)) {
                    $uploadedFiles[$category][] = $filePath;
                } else {
                    echo "Failed to upload file: " . $_FILES[$category]['name'][$key];
                    exit;
                }
            }
        }
    }

    // Define function to convert images to Base64
    function base64EncodeImage($imagePath) {
        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
        $data = file_get_contents($imagePath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // Encode the images
    $logoBase64 = base64EncodeImage('https://www.solucomp.com/cop/logo.png');
    $imageBase64 = base64EncodeImage('https://www.solucomp.com/cop/imageDummy2.jpg');

    // Get today's date in the format dd-mm-yyyy
    $today_date = date('d-m-Y');

    // Read the HTML template
    $htmlTemplate = file_get_contents('page_1.html');
    if ($htmlTemplate === false) {
        echo "Error: Unable to read the HTML template file.";
        exit;
    }

    // Replace placeholders with actual data and encoded images
    $htmlContent = str_replace(
        ['{{SITE_NAME}}', '{{TECH_NAME}}', '{{ENGINEER_CONTACT}}', '{{CUSTOMER_ENGINEER_NAME}}', '{{logo}}', '{{image_path}}', '{{today_date}}'],
        [$site_n, $solucomp_t, $engineer_co, $customer_engin, $logoBase64, $imageBase64, $today_date],
        $htmlTemplate
    );

    $htmlTemplate2 = file_get_contents('page_2.html');
    if ($htmlTemplate2 === false) {
        echo "Error: Unable to read the index3.html file.";
        exit;
    }

    $htmlContent2 = str_replace(
        ['{{logo}}','{{today_date}}'],
        [ $logoBase64, $today_date],
        $htmlTemplate2
    );

    
    $mergedhtmlContent = $htmlContent .  $htmlContent2;

    $htmlTemplate3 = file_get_contents('page_3.html');
    $pageNum = 3;
    foreach ($categories as $category) {
        if (isset($uploadedFiles[$category]) && !empty($uploadedFiles[$category])) {
            $heading = ucwords(str_replace('_', ' ', $category));
            
            for ($i = 0; $i < count($uploadedFiles[$category]); $i += 2) {
                $img1 = isset($uploadedFiles[$category][$i]) ? base64EncodeImage(realpath($uploadedFiles[$category][$i])): '';
                $img2 = isset($uploadedFiles[$category][$i + 1]) ? base64EncodeImage(realpath($uploadedFiles[$category][$i + 1])) : '';
                
                $htmlContent3 = str_replace(
                    ['{{logo}}', '{{heading}}', '{{img1}}', '{{img2}}', '{{today_date}}', '{{count}}'],
                    [$logoBase64, $heading, $img1, $img2, $today_date, $pageNum],
                    $htmlTemplate3
                );
                $mergedhtmlContent .= $htmlContent3;
                $pageNum++;
            }
        }
    }



    $dompdf->loadHtml($mergedhtmlContent);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    // $dompdf->stream("sample.pdf", array("Attachment" => false));
    // Save the generated PDF to a file
    $outputFilename = 'output.pdf';
    $dompdf->output(array('compress' => 0)); // Output the PDF without compressing
    file_put_contents($outputFilename, $dompdf->output()); // Save the PDF to file

    // Optionally, provide the file for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $outputFilename . '"');
    header('Content-Length: ' . filesize($outputFilename));
    readfile($outputFilename);

    // Clean up: remove the generated PDF file
    unlink($outputFilename);

    
    exit;
}
?>
