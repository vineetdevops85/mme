<?php
require 'vendor/autoload.php'; // Include Composer's autoloader

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $excelFile = $_FILES['excel_file'];

    // Check if file is uploaded successfully
    if ($excelFile['error'] === UPLOAD_ERR_OK) {
        $inputFileName = $excelFile['tmp_name'];
        $spreadsheet = IOFactory::load($inputFileName);

        // Check if the sheet name is "MME"
        $sheetName = 'MME';
        if (!$spreadsheet->getSheetByName($sheetName)) {
            die("Please check sheet name. Expected sheet name: $sheetName");
        }

        // Get the MME sheet
        $sheet = $spreadsheet->getSheetByName($sheetName);

        // Get the highest row number
        $highestRow = $sheet->getHighestRow();

        // Get the value for $name from the input field
        $name = $_POST['textbox_input'];

        // Initialize the string
        $string = "";

        // Loop through rows starting from row 2 (assuming row 1 contains headers)
        for ($row = 2; $row <= $highestRow; $row++) {
            $ip1 = $sheet->getCell('C' . $row)->getValue();
            $ip2 = $sheet->getCell('D' . $row)->getValue();
            $mme = $sheet->getCell('E' . $row)->getValue();

            // Check if any value is missing and replace with placeholder
            $ip1 = empty($ip1) ? '' : $ip1;
            $ip2 = empty($ip2) ? '' : $ip2;

            // Replace <textbox> with user input
            $ip1 = str_replace('<textbox>', $_POST['textbox_input'], $ip1);
            $ip2 = str_replace('<textbox>', $_POST['textbox_input'], $ip2);

            // Append data to the string
            $string .= "CREATE\n";
            $string .= "FDN : \"SubNetwork=ONRM_ROOT_MO,MeContext=$name,ManagedElement=$name,ENodeBFunction=1,TermPointToMme=\"$mme\"\n";
            $string .= "additionalCnRef : <empty>\n";
            $string .= "administrativeState : UNLOCKED\n";
            $string .= "dcnType : DEFAULT\n";
            $string .= "domainName : \"\"\n";
            $string .= "ipAddress1 : \"0.0.0.0\"\n";
            $string .= "ipAddress2 : \"0.0.0.0\"\n";
            $string .= "mmeSupportLegacyLte : true\n";
            $string .= "termPointToMmeId : \"$mme\"\n";
            $string .= "mmeSupportNbIoT : true\n";
            $string .= "ipv6Address1 : \"$ip1\"\n";
            $string .= "ipv6Address2 : \"$ip2\"\n\n"; // Add a new line after each set of data
        }

        // Generate the text file
        $outputFileName = $name . "_MME.txt";
        file_put_contents($outputFileName, $string);

        // Force download the generated text file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($outputFileName) . '"');
        readfile($outputFileName);

        // Delete the text file after download
        unlink($outputFileName);

        exit; // Stop script execution after generating and downloading the text file
    } else {
        echo 'Error uploading file.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nokia to Ericsson MME</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            color: white;
            background-color: blue;
            border-radius: 5px;
            margin-bottom: 40px;
        }
        /* 
        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        input[type="file"],
        input[type="text"],
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="text"]::placeholder {
            color: #999;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        } */
    </style>
</head>
<body>
    <div class="container">
        <h3 align="center">Nokia to Ericsson MME</h3>
        <form method="post" enctype="multipart/form-data">
        <div class="form-group">
        <label for="exampleInputPassword1">Choose Nokia MME Input:</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xls,.xlsx">
                <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Ericsson Site ID:</label>
            <input type="text" class="form-control" id="textbox_input" name="textbox_input" placeholder="Enter Ericsson Site ID">
        </div>
        <button type="submit" class="btn btn-success">Download Script</button>
        </form>
    </div>
</body>
</html>