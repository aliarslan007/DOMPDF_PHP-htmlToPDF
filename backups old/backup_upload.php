<?php

require 'vendor/autoload.php'; // Include Composer's autoloader

use mikehaertl\wkhtmlto\Pdf;


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
    $site_n = $_POST['site_name'];
    $solucomp_t = $_POST['solucomp_tech'];
    $engineer_co = $_POST['engineer_contact'];
    $customer_engin = $_POST['customer_engineer_name'];

    $uploadDir = 'uploads/';
    $categories = ['post_install_pics', 'material_pics', 'pre_install_pics'];
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
                }
            }
        }
    }

    // Define absolute paths for resources
    $logoPath = 'file:///' . str_replace('\\', '/', realpath('C:/xampp/htdocs/php_pdf_generator/logo.png'));
    $imagePath = 'file:///' . str_replace('\\', '/',realpath('C:/xampp/htdocs/php_pdf_generator/image-8.jpg')); // Use forward slashes
    
    // Check paths and update placeholders
    if (!$logoPath) {
        echo "Error: Logo file not found.";
        exit;
    }
    if (!$imagePath) {
        echo "Error: Image file not found.";
        exit;
    }
   
    $pdf = new Pdf([
        'binary' => 'G:\\ali_softwares_of_C_drive\\wkhtmltopdf\\bin\\wkhtmltopdf.exe', // Adjust path accordingly
        'no-outline', // Make Chrome not complain
        'margin-top'    => 0,
        'margin-right'  => 0,
        'margin-bottom' => 0,
        'margin-left'   => 0,
        'enable-local-file-access', // Allow local file access
        'no-stop-slow-scripts', // Prevent stopping of slow scripts
        'load-error-handling' => 'ignore', // Ignore load errors
    ]);
    // Get today's date in the format dd-mm-yyyy
    $today_date = date('d-m-Y');
    // Read the HTML template
    $htmlTemplate = file_get_contents('page_1.html');
    if ($htmlTemplate === false) {
        echo "Error: Unable to read the HTML template file.";
        exit;
    }

    // Replace placeholders with actual data and relative paths
    $htmlContent = str_replace(
        ['{{SITE_NAME}}', '{{TECH_NAME}}', '{{ENGINEER_CONTACT}}', '{{CUSTOMER_ENGINEER_NAME}}', '{{logo}}', '{{image_path}}', '{{today_date}}'],
        [$site_n, $solucomp_t, $engineer_co, $customer_engin, $logoPath, $imagePath, $today_date],
        $htmlTemplate
    );

    $pdf->addPage($htmlContent);


    $htmlTemplate2 = file_get_contents('page_2.html');
    if ($htmlTemplate2 === false) {
        echo "Error: Unable to read the index3.html file.";
        exit;
    }

    $htmlContent2 = str_replace(
        ['{{logo}}','{{today_date}}'],
        [ $logoPath, $today_date],
        $htmlTemplate2
    );

    $pdf->addPage($htmlContent2);
    $pageNum = 3;
    // Page 3 and onward for images
    $htmlTemplate3 = file_get_contents('page_3.html');
    foreach ($categories as $category) {
        if (isset($uploadedFiles[$category]) && !empty($uploadedFiles[$category])) {
            $heading = ucwords(str_replace('_', ' ', $category));
            
            for ($i = 0; $i < count($uploadedFiles[$category]); $i += 2) {
                $img1 = isset($uploadedFiles[$category][$i]) ? 'file:///' . str_replace('\\', '/', realpath($uploadedFiles[$category][$i])) : '';
                $img2 = isset($uploadedFiles[$category][$i + 1]) ? 'file:///' . str_replace('\\', '/', realpath($uploadedFiles[$category][$i + 1])) : '';
                $htmlContent3 = str_replace(
                    ['{{logo}}', '{{heading}}', '{{img1}}', '{{img2}}', '{{today_date}}', '{{count}}'],
                    [$logoPath, $heading, $img1, $img2, $today_date, $pageNum],
                    $htmlTemplate3
                );
                $pdf->addPage($htmlContent3);
                $pageNum++;
            }
        }
    }

    // Set PDF output path
    $pdfOutputPath = sys_get_temp_dir() . '/generated_form.pdf';

    // Save PDF to file
    if (!$pdf->saveAs($pdfOutputPath)) {
        $error = $pdf->getError();
        echo "Error saving PDF: " . $error . "<br>";
        echo "Command executed: " . $pdf->getCommand() . "<br>"; // Output the command being executed
        exit;
    }
    

    // Provide download link
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="generated_form.pdf"');
    readfile($pdfOutputPath);

    // Clean up temporary files
    unlink($pdfOutputPath);
    exit;



    // $mergedHtmlContent = $htmlContent . '<div style="page-break-after: always;"></div>' . $htmlContent2;

    // // Save the merged HTML to a temporary file
    // $tempHtmlFile = tempnam(sys_get_temp_dir(), 'html_') . '.html';

    // if (file_put_contents($tempHtmlFile, $mergedHtmlContent) === false) {
    //     echo "Error: Unable to write temporary HTML file.";
    //     exit;
    // }

    // // Output PDF file path
    // $pdfOutputPath = sys_get_temp_dir() . '/generated_form.pdf';

    // // Command to convert HTML to PDF using wkhtmltopdf
    // $command = escapeshellarg($path_wkhtmltopdf) . " --enable-local-file-access " . escapeshellarg($tempHtmlFile) . " " . escapeshellarg($pdfOutputPath);
    // echo "Executing command: $command<br>";

    // // Execute the command and capture output
    // exec($command . " 2>&1", $output, $return_var);
    // echo "Command output: " . implode("<br>", $output) . "<br>";

    // if ($return_var === 0) {
    //     // PDF generated successfully, provide a download link
    //     header('Content-Type: application/pdf');
    //     header('Content-Disposition: attachment; filename="generated_form.pdf"');
    //     readfile($pdfOutputPath);
    // } else {
    //     echo "Error generating PDF. Command returned status code $return_var.<br>";
    // }

    // // Clean up temporary files
    // unlink($tempHtmlFile);
    // if (file_exists($pdfOutputPath)) {
    //     unlink($pdfOutputPath);
    // }
    // exit;

    // foreach ($categories as $category) {
    //     if (isset($uploadedFiles[$category]) && !empty($uploadedFiles[$category])) {
    //         $heading = ucwords(str_replace('_', ' ', $category));
            
    //         for ($i = 0; $i < count($uploadedFiles[$category]); $i += 2) {
    //             $img1 = isset($uploadedFiles[$category][$i]) ? base64EncodeImage('file:///' . str_replace('\\', '/', realpath($uploadedFiles[$category][$i])) ): '';
    //             $img2 = isset($uploadedFiles[$category][$i + 1]) ? base64EncodeImage('file:///' . str_replace('\\', '/', realpath($uploadedFiles[$category][$i + 1]))) : '';
                
    //             $htmlContent3 = str_replace(
    //                 ['{{logo}}', '{{heading}}', '{{img1}}', '{{img2}}', '{{today_date}}', '{{count}}'],
    //                 [$logoBase64, $heading, $img1, $img2, $today_date, $pageNum],
    //                 $htmlTemplate3
    //             );
    //             $mergedhtmlContent = $mergedhtmlContent . $htmlContent3;
    //             $pageNum++;
    //         }
    //     }
    // }
}
?>
