<?php
require '../includes/dbcon.php';

function insertupdatedbf($conn, $vendor, $upc, $sku, $brand, $model, $desc, $srp, $promo, $remarks)
{
    // Use prepared statements to prevent SQL injection
    $selectQuery = "SELECT * FROM tbl_uploaded_dbf WHERE upc = ?";
    $selectStmt = $conn->prepare($selectQuery);
    $selectStmt->bind_param("s", $upc);
    $selectStmt->execute();
    $result = $selectStmt->get_result();

    if ($result->num_rows === 0) {
        $selectStmt->close(); // Close before inserting

        // Insert new record
        $insertQuery = "INSERT INTO tbl_uploaded_dbf (vendor, upc, sku, brand, model, `desc`, srp, promo, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sssssssss", $vendor, $upc, $sku, $brand, $model, $desc, $srp, $promo, $remarks);
        $insertStmt->execute();
        $insertStmt->close();
        return 'inserted';

    } else {
        $existingRecord = $result->fetch_assoc();
        $selectStmt->close(); // Close before any return or update

        if (
            $existingRecord['sku'] != $sku ||
            $existingRecord['vendor'] != $vendor ||
            $existingRecord['brand'] != $brand ||
            $existingRecord['model'] != $model ||
            $existingRecord['desc'] != $desc ||
            $existingRecord['srp'] != $srp ||
            $existingRecord['promo'] != $promo ||
            $existingRecord['remarks'] != $remarks
        ) {
            $updateQuery = "UPDATE tbl_uploaded_dbf SET sku = ?, vendor = ?, brand = ?, model = ?, `desc` = ?, srp = ?, promo = ?, remarks = ? WHERE upc = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("sssssssss", $sku, $vendor, $brand, $model, $desc, $srp, $promo, $remarks, $upc);
            $updateStmt->execute();
            $updateStmt->close();
            return 'updated';
        } else {
            return 'retained';
        }
    }
}

function response($status_code, $status_msg)
{
    echo json_encode(
        array(
            'status_code' => $status_code,
            'status_msg' => $status_msg
        )
    );
}
function responseDone($array, $msg = "")
{
    echo json_encode(
        array(
            'data' => $array,
            'status_code' => '200',
            'status_msg' => $msg
        )
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile']) && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'dbf':
            require 'vendor/autoload.php';
            $today = date("Y-m-d");
            $uploadedFile = $_FILES['excelFile'];
            $uploadPath = './uploads/';

            if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
                $tmpName = $uploadedFile['tmp_name'];
                $fileName = basename($uploadedFile['name']);
                $uploadFilePath = $uploadPath . $fileName;

                if (move_uploaded_file($tmpName, $uploadFilePath)) {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadFilePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $highestRow = $worksheet->getHighestRow();
                    $insertedCount = 0;

                    for ($row = 2; $row <= $highestRow; $row++) {
                        $vendor = $worksheet->getCell('A' . $row)->getValue();
                        $upc = $worksheet->getCell('B' . $row)->getValue();
                        $sku = $worksheet->getCell('C' . $row)->getValue();
                        $brand = $worksheet->getCell('D' . $row)->getValue();
                        $model = $worksheet->getCell('E' . $row)->getValue();
                        $desc = $worksheet->getCell('F' . $row)->getValue();
                        $srp = $worksheet->getCell('G' . $row)->getValue();
                        $promo = $worksheet->getCell('H' . $row)->getValue();
                        $remarks = $worksheet->getCell('I' . $row)->getValue();

                        if ($vendor !== null && $upc !== null && $sku !== null && $brand !== null) {
                            $success =insertupdatedbf($conn, $vendor, $upc, $sku, $brand, $model, $desc, $srp, $promo, $remarks);

                            if ($success) {
                                $insertedCount++;
                            }
                        }
                    }

                    $msg = "Promotion data processed: $insertedCount inserted.";
                    response('200', $msg);
                } else {
                    response('404', 'Error moving the uploaded file.');
                }
            } else {
                response('404', 'Error uploading the file.');
            }

            break;
        default:
            response('404', 'Something went wrong, invalid action.');
    }
} else {
    response('404', 'Something went wrong, parameters missing.');
}
