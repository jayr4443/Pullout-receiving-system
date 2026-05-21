<?php
include 'dbcon.php';
switch ($_POST['action']) {
  //LOGIN
  case "login":
    login($conn);
    break;
  //TRANSACT
  case "searchpoa":
    searchpoa($conn);
    break;
  case "transactpoa":
    transactpoa($conn);
    break;
  case "transactpoaview":
    transactpoaview($conn);
    break;
  case "scanupc":
    scanupc($conn);
    break;
  case "saveTransaction":
    saveTransaction($conn);
    break;
  case "checkpoaexist":
    checkpoaexist($conn);
    break;
  case "loadParkedPOAs":
    loadParkedPOAs($conn);
    break;
  case "loadfloatItems":
    loadfloatItems($conn);
    break;
  case "updatefloat":
    updatefloat($conn);
    break;
  case "updateTransactions":
    updateTransactions($conn);
    break;
  case "getTransactPOAs":
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $itemsPerPage = isset($_POST['itemsPerPage']) ? intval($_POST['itemsPerPage']) : 10;
    getTransactPOAs($conn, $page, $itemsPerPage);
    break;
  //USER
  case "adduser":
    adduser($conn);
    break;
  case "updateuser":
    updateuser($conn);
    break;
  case "deleteuser":
    deleteuser($conn);
    break;
  case "gettbluser":
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $itemsPerPage = isset($_POST['itemsPerPage']) ? intval($_POST['itemsPerPage']) : 10;
    gettbluser($conn, $page, $itemsPerPage);
    break;
  //DBF
  case "gettbldbf":
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $itemsPerPage = isset($_POST['itemsPerPage']) ? intval($_POST['itemsPerPage']) : 10;
    gettbldbf($conn, $page, $itemsPerPage);
    break;
  default:
  responseError(mysqli_error($conn), 'Something went wrong, parameters missing');
  break;
}
//LOGIN
function login($conn)
{
    // Grab and escape POSTed credentials
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password = mysqli_real_escape_string($conn, $_POST['password'] ?? '');

    // Look for a matching user
    $sql = "
      SELECT id, fname, username, userlevel
      FROM tbl_user
      WHERE username = '$username'
        AND `password` = '$password'
      LIMIT 1
    ";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        // User found
        $user = mysqli_fetch_assoc($result);
        responseDone(
            // wrap in array so 'data' is an array of one user
            [ $user ],
            "Login successful"
        );
    } else {
        // No match
        responseError([], "Invalid username or password");
    }
}


//Transaction
function searchpoa($conn)
{
    $term = mysqli_real_escape_string($conn, $_POST['term']);
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $poastore = mysqli_real_escape_string($conn, $_POST['poastore']);
    $poaSku = mysqli_real_escape_string($conn, $_POST['poaSku']);
    $poamtcode = mysqli_real_escape_string($conn, $_POST['poamtcode']);
    $sql = '';
    $column = '';

    if ($search === 'POA') {
        $sql = "SELECT DISTINCT poa FROM tbl_uploaded_poa WHERE poa LIKE '%$term%' AND (stat IS NULL OR stat = '') LIMIT 10";
        $column = 'poa';
    } else  if ($search === 'float') {
      $sql = "SELECT DISTINCT poa FROM tbl_uploaded_poa WHERE shiptoname = '$poastore' AND sku = '$poaSku' AND icode = '$poamtcode' AND poa LIKE '%$term%' AND (stat IS NULL OR stat = '') LIMIT 10";
      $column = 'poa';
    } else if ($search === 'add') {
      $sql = "SELECT DISTINCT poa FROM tbl_transation WHERE remarks = 'complete' AND poa LIKE '%$term%' LIMIT 10";
      $column = 'poa';
    }

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $suggestions = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $suggestions[] = array(
                'label' => $row[$column],
                'value' => $row[$column]
            );
        }
        responseDone($suggestions);
    } else {
        responseError([], mysqli_error($conn));
    }
}

function transactpoaview($conn)
{
    $poa = mysqli_real_escape_string($conn, $_POST['poa']);
    $rmk = mysqli_real_escape_string($conn, $_POST['rmk']);

    // Fetch records matching provided POA and remarks
    $sql = "SELECT id, poa, materialcode, shiptoname, sku, scanqty, totalqty, remarks, stat
            FROM tbl_transation
            WHERE poa = '$poa' AND remarks = '$rmk'
            ORDER BY id DESC";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = array(
                'id'=> $row['id'],
                'poa'=> $row['poa'],
                'materialcode' => $row['materialcode'],
                'shiptoname' => $row['shiptoname'],
                'sku' => $row['sku'],
                'scanqty' => $row['scanqty'],
                'totalqty' => $row['totalqty'],
                'stat' => $row['stat'],
                'remarks' => $row['remarks']
            );
        }
        responseDone($data);
    } else {
        responseError([], mysqli_error($conn));
    }
}


function loadParkedPOAs($conn)
{
  $sql = "SELECT poa, shiptoname, dtcparked, remarks FROM tbl_transation WHERE remarks = 'park' GROUP BY poa ORDER BY id desc";
    $result = mysqli_query($conn, $sql);

    if ($result) {
      $data = array();
      while ($row = mysqli_fetch_assoc($result)) {
          $data[] = array(
              'poa' => $row['poa'],
              'dtcparked' => $row['dtcparked'],
              'remarks' => $row['remarks'],
              'shiptoname' => $row['shiptoname']
          );
      }
      responseDone($data);
    } else {
        responseError([], mysqli_error($conn));
    }
}
function loadfloatItems($conn)
{
  $sql = "SELECT id, poa, shiptoname, materialcode, sku, scanqty,  dtc FROM tbl_transation WHERE stat = 'float' ORDER BY id desc";
    $result = mysqli_query($conn, $sql);

    if ($result) {
      $data = array();
      while ($row = mysqli_fetch_assoc($result)) {
          $data[] = array(
            'id' => $row['id'],
              'shiptoname' => $row['shiptoname'],
              'materialcode' => $row['materialcode'],
              'sku' => $row['sku'],
              'scanqty' => $row['scanqty'],
              'dtc' => $row['dtc']
          );
      }
      responseDone($data);
    } else {
        responseError([], mysqli_error($conn));
    }
}
function updatefloat($conn)
{
    // Safely handle incoming POST data
    $poa = mysqli_real_escape_string($conn, $_POST['poa'] ?? '');
    $id = $_POST['id'] ?? '';
    $store = mysqli_real_escape_string($conn, $_POST['store'] ?? '');
    $sku = mysqli_real_escape_string($conn, $_POST['sku'] ?? '');
    $today = date('Y-m-d H:i:s');

    // Validate required fields
    if (empty($poa) || empty($id) || empty($store) || empty($sku)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        return;
    }

    // 1. Check if the POA exists in the tbl_uploaded_poa table with stat IS NULL
    $checkQuery = "SELECT poa, qnty FROM tbl_uploaded_poa WHERE poa = '$poa' AND sku = '$sku' AND shiptoname = '$store'  AND (stat IS NULL OR stat = '')  LIMIT 1";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) == 0) {
        // If no record found or POA already used, return an error
        echo json_encode(['status' => 'error', 'message' => 'POA Number not Uploaded.']);
        return;
    }

    // Get the quantity from the found record
    $row = mysqli_fetch_assoc($result);
    $quantity = $row['qnty'];

    // 2. Update the POA and totalqty in the tbl_transation table
    $updateSql = "UPDATE tbl_transation SET poa = '$poa', totalqty = '$quantity', stat = 'complete', remarks = 'float', dtccomp = '$today' WHERE id = '$id'";
    if (mysqli_query($conn, $updateSql)) {
        // 3. If the first update is successful, update the POA status in tbl_uploaded_poa
        $updateSql2 = "UPDATE tbl_uploaded_poa SET stat = '1' WHERE poa = '$poa' AND sku = '$sku' AND shiptoname = '$store' AND (stat IS NULL OR stat = '')";
        if (mysqli_query($conn, $updateSql2)) {
            // If the second update is successful, send a success response
            echo json_encode(['status' => 'success', 'message' => "POA number: $poa has been successfully saved."]);
        } else {
            // Error updating POA status in tbl_uploaded_poa
            echo json_encode(['status' => 'error', 'message' => "Error updating POA status: " . mysqli_error($conn)]);
        }
    } else {
        // Error updating POA in tbl_transation
        echo json_encode(['status' => 'error', 'message' => "Error updating POA in tbl_transation: " . mysqli_error($conn)]);
    }
}
function updateTransactions($conn)
{
    // Safely handle incoming POST data
    $id = $_POST['id'] ?? '';
    $qty = mysqli_real_escape_string($conn, $_POST['qty'] ?? '');
    $usr = mysqli_real_escape_string($conn, $_POST['usr'] ?? '');
    $today = date('Y-m-d H:i:s');

    // Make sure ID is not empty before proceeding
    if (!empty($id)) {
        $sql = "UPDATE tbl_transation
                SET scanqty = '$qty', updateby = '$usr', dtcupdate = '$today'
                WHERE id = '$id'";

        if (mysqli_query($conn, $sql)) {
            responseDone("Update successfully");
        } else {
            responseError([], mysqli_error($conn));
        }
    } else {
        responseError([], "Invalid ID provided");
    }
}

function checkpoaexist($conn)
{
    $poa = mysqli_real_escape_string($conn, $_POST['term']);
    $search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';

    // Case 1: Late upload / forgotten POA check
    if ($search != '1') {
        $checkUploaded = "
            SELECT poa
            FROM tbl_uploaded_poa
            WHERE poa = '$poa'
              AND (stat IS NULL OR stat = '')
            LIMIT 1";

        $result = mysqli_query($conn, $checkUploaded);

        if (mysqli_num_rows($result) === 0) {
            responseError([], 'POA not available.');
            return;
        }

        responseDone([], 'POA is valid.');
        return;
    }

    // Case 2: Normal transaction check
    // 1. Check if already transacted
    $checkTransaction = "
        SELECT poa
        FROM tbl_transation
        WHERE poa = '$poa'
        LIMIT 1";

    $result1 = mysqli_query($conn, $checkTransaction);

    if (mysqli_num_rows($result1) > 0) {
        responseError([], 'Already transacted.');
        return;
    }

    // 2. Check if POA exists and is not used
    $checkUploaded = "
        SELECT poa
        FROM tbl_uploaded_poa
        WHERE poa = '$poa'
          AND (stat IS NULL OR stat = '')
        LIMIT 1";

    $result2 = mysqli_query($conn, $checkUploaded);

    if (mysqli_num_rows($result2) === 0) {
        responseError([], 'POA not available.');
        return;
    }

    // 3. POA is valid for transaction
    responseDone([], 'POA is valid.');
}


function transactpoa($conn)
{
    $poa = mysqli_real_escape_string($conn, $_POST['poa']);
    $transact = $_POST['transact'] ?? '';
    $sql = '';

    // Step 1: Check if POA exists
    $checkPoa = "SELECT 1 FROM tbl_uploaded_poa WHERE poa = '$poa' LIMIT 1";
    $poaExists = mysqli_query($conn, $checkPoa);
    if (!$poaExists || mysqli_num_rows($poaExists) == 0) {
        responseError([], "POA not found.");
        return;
    }

    if ($transact === 'new') {
        // Step 2: Look for float transactions
        $floatSql = "SELECT sku, materialcode, shiptoname, scanqty FROM tbl_transation WHERE stat = 'float'";
        $floatResult = mysqli_query($conn, $floatSql);

        if ($floatResult && mysqli_num_rows($floatResult) > 0) {
            $data = [];

            while ($transRow = mysqli_fetch_assoc($floatResult)) {
                $sku = $transRow['sku'];
                $materialcode = $transRow['materialcode'];
                $shiptoname = $transRow['shiptoname'];
                $scanqty = $transRow['scanqty'];

                // Try to find a matching row in uploaded POA
                $uploadSql = "SELECT icode, shiptoname, sku, qnty
                              FROM tbl_uploaded_poa
                              WHERE poa = '$poa'
                              AND (stat IS NULL OR stat = '')
                              AND icode = '$materialcode'
                              AND sku = '$sku'
                              AND shiptoname = '$shiptoname'
                              LIMIT 1";
                $uploadResult = mysqli_query($conn, $uploadSql);

                if ($uploadResult && mysqli_num_rows($uploadResult) > 0) {
                    $uploadRow = mysqli_fetch_assoc($uploadResult);

                    // ✅ Update tbl_transation with POA
                    $updateSql = "UPDATE tbl_transation
                                  SET poa = '$poa'
                                  WHERE materialcode = '$materialcode'
                                  AND sku = '$sku'
                                  AND shiptoname = '$shiptoname'
                                  AND (poa IS NULL OR poa = '')";
                    mysqli_query($conn, $updateSql);

                    $data[] = [
                        'material_code' => $uploadRow['icode'],
                        'sku' => $uploadRow['sku'],
                        'required_qty' => $uploadRow['qnty'],
                        'scanned_qty' => $scanqty,
                        'shiptoname' => $uploadRow['shiptoname']
                    ];
                }
            }

            // If any match found
            if (!empty($data)) {
                responseDone($data);
                return;
            }
        }

        // Step 3: Fallback if no match found - get uploaded POA only
        $sql = "SELECT icode, shiptoname, sku, qnty
                FROM tbl_uploaded_poa
                WHERE poa = '$poa'
                AND (stat IS NULL OR stat = '')";
    }

    // Step 4: Parked logic
    elseif ($transact === 'parked') {
        $sql = "SELECT materialcode, shiptoname, sku, totalqty, scanqty, stat
                FROM tbl_transation
                WHERE poa = '$poa' AND remarks = 'park'";
    }

    // Step 5: Execute and return fallback or parked
    if ($sql) {
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                if ($transact === 'new') {
                    $data[] = [
                        'material_code' => $row['icode'],
                        'sku' => $row['sku'],
                        'required_qty' => $row['qnty'],
                        'scanned_qty' => 0,
                        'shiptoname' => $row['shiptoname']
                    ];
                } elseif ($transact === 'parked') {
                    $data[] = [
                        'material_code' => $row['materialcode'],
                        'sku' => $row['sku'],
                        'required_qty' => $row['totalqty'],
                        'scanned_qty' => $row['scanqty'],
                        'shiptoname' => $row['shiptoname'],
                        'stat' => $row['stat']
                    ];
                }
            }

            responseDone($data);
            return;
        } else {
            responseError([], mysqli_error($conn));
            return;
        }
    }

    responseError([], 'No data to fetch.');
}



function saveTransaction($conn)
{
    $poa = mysqli_real_escape_string($conn, $_POST['poa'] ?? '');
    $sku = mysqli_real_escape_string($conn, $_POST['sku'] ?? '');
    $materialcode = mysqli_real_escape_string($conn, $_POST['materialcode'] ?? '');
    $scanqty = mysqli_real_escape_string($conn, $_POST['scanqty'] ?? '0');
    $totalqty = mysqli_real_escape_string($conn, $_POST['totalqty'] ?? '0');
    $stat = mysqli_real_escape_string($conn, $_POST['stat'] ?? '');
    $trmk = mysqli_real_escape_string($conn, $_POST['trmk'] ?? '');
    $nm = mysqli_real_escape_string($conn, $_POST['nm'] ?? '');
    $shiptoname = mysqli_real_escape_string($conn, $_POST['shiptoname'] ?? '');
    $transact = $_POST['transact'] ?? '';

    if ($transact == 'complete') {
          $sql = "CALL sp_save_transaction('$poa', '$shiptoname', '$sku', '$materialcode', '$scanqty', '$totalqty', '$stat', '$trmk', '$nm')";
          if (mysqli_query($conn, $sql)) {
              $updateSql = "UPDATE tbl_uploaded_poa SET stat = '1' WHERE poa = '$poa'";
              if (mysqli_query($conn, $updateSql)) {
                  responseDone($sql);
              } else {
                  responseError([], "Error updating POA status: " . mysqli_error($conn));
              }
          } else {
              responseError([], "Error calling save stored procedure: " . mysqli_error($conn));
          }
    } elseif ($transact === 'park') {
            // ✅ Use sp_park_transaction with correct parameters
            $sql = "CALL sp_park_transaction('$poa', '$shiptoname', '$sku', '$materialcode', '$scanqty', '$totalqty', '$stat', '$trmk', '$trmk', '$nm')";
            if (mysqli_query($conn, $sql)) {
                $updateSql = "UPDATE tbl_uploaded_poa SET stat = '2' WHERE poa = '$poa'";
                if (mysqli_query($conn, $updateSql)) {
                    responseDone([], "POA number: $poa has been successfully parked.");
                } else {
                    responseError([], "Error updating POA status: " . mysqli_error($conn));
                }
            } else {
                responseError([], "Error calling park stored procedure: " . mysqli_error($conn));
            }
          }
}

// function saveTransaction($conn)
// {
//     $poa = mysqli_real_escape_string($conn, $_POST['poa'] ?? '');
//     $sku = mysqli_real_escape_string($conn, $_POST['sku'] ?? '');
//     $materialcode = mysqli_real_escape_string($conn, $_POST['materialcode'] ?? '');
//     $scanqty = mysqli_real_escape_string($conn, $_POST['scanqty'] ?? '0');
//     $totalqty = mysqli_real_escape_string($conn, $_POST['totalqty'] ?? '0');
//     $stat = mysqli_real_escape_string($conn, $_POST['stat'] ?? '');
//     $trmk = mysqli_real_escape_string($conn, $_POST['trmk'] ?? ''); // ✅ Transaction remarks from dropdown
//     $nm = mysqli_real_escape_string($conn, $_POST['nm'] ?? '');
//     $shiptoname = mysqli_real_escape_string($conn, $_POST['shiptoname'] ?? '');

//         // ✅ Use sp_save_transaction including `trmk`
//         $sql = "CALL sp_save_transaction('$poa', ' $shiptoname', '$sku', '$materialcode', '$scanqty', '$totalqty', '$stat', '$trmk', '$nm')";
//         if (mysqli_query($conn, $sql)) {
//             $updateSql = "UPDATE tbl_uploaded_poa SET stat = '1' WHERE poa = '$poa'";
//             if (mysqli_query($conn, $updateSql)) {
//                 responseDone([], "POA number: $poa has been successfully saved.");
//             } else {
//                 responseError([], "Error updating POA status: " . mysqli_error($conn));
//             }
//         } else {
//             responseError([], "Error calling save stored procedure: " . mysqli_error($conn));
//         }

//       // } elseif ($transact === 'park') {
//       //   // ✅ Use sp_park_transaction with correct parameters
//       //   $sql = "CALL sp_park_transaction('$poa', '$shiptoname', '$sku', '$materialcode', '$scanqty', '$totalqty', '$stat', '$trmk', '$trmk', '$nm')";
//       //   if (mysqli_query($conn, $sql)) {
//       //       $updateSql = "UPDATE tbl_uploaded_poa SET stat = '2' WHERE poa = '$poa'";
//       //       if (mysqli_query($conn, $updateSql)) {
//       //           responseDone([], "POA number: $poa has been successfully parked.");
//       //       } else {
//       //           responseError([], "Error updating POA status: " . mysqli_error($conn));
//       //       }
//       //   } else {
//       //       responseError([], "Error calling park stored procedure: " . mysqli_error($conn));
//       //   }
//     // } elseif ($isFloat) {
//     //   // ✅ Use sp_park_transaction with correct parameters
//     //   $sql = "CALL sp_float_transaction('$poa', '$sku', '$materialcode', '$scanqty', '$totalqty', '$stat', '$trmk', '$nm')";
//     //   if (mysqli_query($conn, $sql)) {
//     //     responseDone([], "Store: $poa has been successfully saved.");
//     //   } else {
//     //       responseError([], "Error calling park stored procedure: " . mysqli_error($conn));
//     //   }
//     // } else {
//     //     responseError([], "Invalid transaction type.");
//     // }
// }


function scanupc($conn)
{
    $upc = $_POST['upc'];
    $poa = $_POST['poa'];


        // 🔵 Normal POA Mode
        $sku = '';

        // Step 1: Try matching by UPC
        $sql = "SELECT sku FROM tbl_uploaded_dbf WHERE upc = '$upc' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result)) {
            $row = mysqli_fetch_assoc($result);
            $sku = $row['sku'];
        }

        // Step 2: If not found, try matching by SKU
        if (!$sku) {
            $sql = "SELECT sku FROM tbl_uploaded_dbf WHERE sku = '$upc' LIMIT 1";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result)) {
                $row = mysqli_fetch_assoc($result);
                $sku = $row['sku'];
            }
        }

        // Step 3: If not found, try matching by model
        if (!$sku) {
            $sql = "SELECT sku FROM tbl_uploaded_dbf WHERE model = '$upc' LIMIT 1";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result)) {
                $row = mysqli_fetch_assoc($result);
                $sku = $row['sku'];
            }
        }

        // Step 4: If still not found, try tbl_uploaded_poa by sku
        if (!$sku) {
            $sql = "SELECT sku FROM tbl_uploaded_poa WHERE sku = '$upc' AND poa = '$poa' LIMIT 1";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result)) {
                $row = mysqli_fetch_assoc($result);
                $sku = $row['sku'];
            }
        }
        // Step 4: If still not found, try tbl_uploaded_poa by icode
        if (!$sku) {
          $sql = "SELECT sku FROM tbl_uploaded_poa WHERE icode = '$upc' AND poa = '$poa' LIMIT 1";
          $result = mysqli_query($conn, $sql);
          if ($result && mysqli_num_rows($result)) {
              $row = mysqli_fetch_assoc($result);
              $sku = $row['sku'];
          }
      }

        // Step 5: If no SKU found at all
        if (!$sku) {
            responseError([ ['icode' => $upc] ], 'UPC, SKU, or Model not found.');
            return;
        }

        // Step 6: Validate found SKU in tbl_uploaded_poa against POA
        $sql = "SELECT icode, sku FROM tbl_uploaded_poa WHERE sku = '$sku' AND poa = '$poa' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result)) {
            $row = mysqli_fetch_assoc($result);
            responseDone([ ['icode' => $row['icode'], 'sku' => $row['sku']] ]);
        } else {
            responseError([ ['icode' => $upc] ], 'Scanned item not in this POA.');
        }

}


function getTransactPOAs($conn, $page, $itemsPerPage)
{
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $offset = ($page - 1) * $itemsPerPage;

    // Base WHERE clause
    $where = "WHERE remarks IN ('park', 'complete', 'float')";
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $where .= " AND poa LIKE '%$search%'";
    }

    // Main query
    $sql = "
        SELECT
            poa,
            shiptoname,
            SUM(scanqty) AS total_scanned,
            SUM(totalqty) AS total_required,
            remarks,
            MAX(userscan) AS userscan,
            MAX(stat) AS stat,
            MAX(dtc) AS dtc
        FROM tbl_transation
        $where
        GROUP BY poa, shiptoname, remarks
        ORDER BY MAX(id) DESC
        LIMIT $offset, $itemsPerPage
    ";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $array = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $array[] = [
                'poa' => $row['poa'],
                'shiptoname' => $row['shiptoname'],
                'total_scanned' => $row['total_scanned'],
                'total_required' => $row['total_required'],
                'remarks' => $row['remarks'],
                'userscan' => $row['userscan'],
                'stat' => $row['stat'],
                'dtc' => $row['dtc']
            ];
        }

        // Count total unique POAs
        $countSql = "SELECT COUNT(DISTINCT poa) as total FROM tbl_transation $where";
        $countResult = mysqli_query($conn, $countSql);
        $countRow = mysqli_fetch_assoc($countResult);
        $totalRows = $countRow['total'];
        $totalPages = ceil($totalRows / $itemsPerPage);

        // Return data
        responseDone1(
            $array,
            [
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]
        );
    } else {
        responseError([], mysqli_error($conn));
    }
}

//User
function adduser($conn)
{
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $userlevel = $_POST['userlevel'];
    $today = date('Y-m-d');

    // Check if the user already exists
    $checkQuery = "SELECT fname FROM tbl_user WHERE fname = '$name'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        responseError([], "The user '$name' already exists.");
    } else {
        $insertQuery = "INSERT INTO tbl_user (fname, username, `password`, userlevel, dtc)
                        VALUES ('$name', '$username', '$password', '$userlevel', '$today')";
        if (mysqli_query($conn, $insertQuery)) {
            responseDone([], "User '$name' has been successfully registered.");
        } else {
            responseError([], "Database error: " . mysqli_error($conn));
        }
    }
}
function updateuser($conn)
{
    $id = $_POST['userid'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $userlevel = $_POST['userlevel'];
    $today = date('Y-m-d');

    $sql = "UPDATE tbl_user
            SET fname = '$name',
                username = '$username',
                `password` = '$password',
                userlevel = '$userlevel',
                updtc = '$today'
            WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        responseDone(array(), "User '$name' has been successfully updated.");
    } else {
        responseError(array(null), mysqli_error($conn));
    }
}

function deleteuser($conn)
{
    $id = $_POST['id'];
    $sql = "DELETE FROM tbl_user WHERE id ='$id'";

   if (mysqli_query($conn, $sql)) {
                responseDone(array(), $sql);
            } else {
                // Handle errors for each query separately
                responseError(array(null), mysqli_error($conn));
            }
}
function gettbluser($conn, $page, $itemsPerPage)
{
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $offset = ($page - 1) * $itemsPerPage;

    $sql = "SELECT id, fname, username, userlevel FROM tbl_user";
    if (!empty($search)) {
        $sql .= " WHERE fname LIKE '%$search%' OR username LIKE '%$search%'";
    }
    $sql .= " ORDER BY id DESC LIMIT $offset, $itemsPerPage";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $array = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $array[] = array(
                'id' => $row['id'],
                'fname' => $row['fname'],
                'username' => $row['username'],
                'userlevel' => $row['userlevel']
            );
        }

        // Calculate total number of rows
        $countSql = "SELECT COUNT(id) as total FROM tbl_user";
        if (!empty($search)) {
            $countSql .= " WHERE fname LIKE '%$search%' OR username LIKE '%$search%'";
        }
        $countResult = mysqli_query($conn, $countSql);
        $countRow = mysqli_fetch_assoc($countResult);
        $totalRows = $countRow['total'];

        $totalPages = ceil($totalRows / $itemsPerPage);

        responseDone1(
            $array,
            array(
                'totalPages' => $totalPages,
                'currentPage' => $page
            )
        );
    } else {
        responseError(array(), mysqli_error($conn));
    }
}

//DBF
function gettbldbf($conn, $page, $itemsPerPage)
{
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $offset = ($page - 1) * $itemsPerPage;

    $sql = "SELECT id, vendor, upc, sku, brand, model, `desc`, srp, promo, remarks FROM tbl_uploaded_dbf";
    if (!empty($search)) {
        $sql .= " WHERE sku LIKE '%$search%' OR upc LIKE '%$search%'";
    }
    $sql .= " ORDER BY id DESC LIMIT $offset, $itemsPerPage";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $array = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $array[] = array(
                'id' => $row['id'],
                'vendor' => $row['vendor'],
                'upc' => $row['upc'],
                'sku' => $row['sku'],
                'brand' => $row['brand'],
                'model' => $row['model'],
                'desc' => $row['desc'],
                'srp' => $row['srp'],
                'promo' => $row['promo'],
                'remarks' => $row['remarks']
            );
        }

        // Calculate total number of rows
        $countSql = "SELECT COUNT(id) as total FROM tbl_uploaded_dbf";
        if (!empty($search)) {
            $countSql .= " WHERE fname LIKE '%$search%' OR username LIKE '%$search%'";
        }
        $countResult = mysqli_query($conn, $countSql);
        $countRow = mysqli_fetch_assoc($countResult);
        $totalRows = $countRow['total'];

        $totalPages = ceil($totalRows / $itemsPerPage);

        responseDone1(
            $array,
            array(
                'totalPages' => $totalPages,
                'currentPage' => $page
            )
        );
    } else {
        responseError(array(), mysqli_error($conn));
    }
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
function responseDone1($array, $pagination, $msg = "")
{
    echo json_encode(
        array(
            'data' => $array,
            'pagination' => $pagination,
            'status_code' => '200',
            'status_msg' => $msg
        )
    );
}


function responseError($array, $msg = "")
{
    echo json_encode(
        array(
            'data' => $array,
            'status_code' => '404',
            'status_msg' => $msg
        )
    );
}
