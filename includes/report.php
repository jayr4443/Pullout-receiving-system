<?php
include 'dbcon.php';

$from = $_GET['dateFrom'] ?? null;
$to = $_GET['dateTo'] ?? null;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="transaction_report.csv"');

$output = fopen('php://output', 'w');

// Correct headers
$headers = ['Creation Date','POA', 'Ship To Name', 'SKU', 'Material Code', 'Material Description', 'Scanned Qty', 'Total Qty', 'Variance', 'Remarks', 'Other Remarks', 'Users','Date Recieved', 'Date Complete', 'Aging (days)'];
fputcsv($output, $headers);

if ($from && $to) {
    $from = $conn->real_escape_string($from);
    $to = $conn->real_escape_string($to);

    $sql = "
        SELECT
            a.dtc,
            a.poa,
            b.shiptoname,
            a.sku,
            a.materialcode,
            b.idesp,
            a.scanqty,
            a.totalqty,
            a.stat,
            a.remarks,
            a.tremarks,
            a.userscan,
            a.dtcparked,
            a.dtccomp
        FROM tbl_transation a
        LEFT JOIN tbl_uploaded_poa b ON a.poa = b.poa AND a.sku = b.sku
        WHERE DATE(a.dtc) BETWEEN '$from' AND '$to' ORDER BY a.poa, a.dtc
    ";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        // Use dtcparked as date received if available, otherwise use dtc
        $dateReceivedRaw = !empty($row['dtcparked']) ? $row['dtcparked'] : $row['dtc'];
        $dateCompleteRaw = $row['dtccomp'];

        // Format the dates to only show YYYY-MM-DD
        $dtc = date('Y-m-d', strtotime($row['dtc']));
        $dtcparked = !empty($row['dtcparked']) ? date('Y-m-d', strtotime($row['dtcparked'])) : '';
        $dtccomp = !empty($row['dtccomp']) ? date('Y-m-d', strtotime($row['dtccomp'])) : '';
        $dateReceived = date('Y-m-d', strtotime($dateReceivedRaw));

        // Calculate variance
        $variance = $row['scanqty'] - $row['totalqty'];

        // Calculate aging (difference in days between received and complete)
        $aging = 0;
        if (!empty($dateReceivedRaw) && !empty($dateCompleteRaw)) {
            $date1 = new DateTime($dateReceivedRaw);
            $date2 = new DateTime($dateCompleteRaw);
            $aging = $date1->diff($date2)->days;
        }

        // Handle Other Remarks logic
        $otherRemarks = (!empty($row['tremarks']) && strtolower($row['stat']) === 'others')
            ? 'statothers ' . $row['tremarks']
            : $row['stat'];

        // Output the formatted row
        fputcsv($output, [
            $dtc,
            $row['poa'],
            $row['shiptoname'],
            $row['sku'],
            $row['materialcode'],
            $row['idesp'],
            $row['scanqty'],
            $row['totalqty'],
            $variance,
            $row['remarks'],
            $otherRemarks,
            $row['userscan'],
            $dateReceived,
            $dtccomp,
            $aging
        ]);
    }

    } else {
        fputcsv($output, ['No records found in selected date range.']);
    }
} else {
    fputcsv($output, ['Missing date parameters.']);
}

fclose($output);
$conn->close();
exit;
