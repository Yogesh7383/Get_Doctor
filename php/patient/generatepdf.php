<?php 
 session_start();

 if (isset($_SESSION["userid"])) {
     if (($_SESSION["userid"]) == "" or $_SESSION['usertype'] != 'PATIENT') {
         header("location: ../login.php");
     } else {
         $userid = $_SESSION["userid"];
     }
 } else {
     header("location: ../login.php");
 }

include('../database_connection.php');
if(isset($_POST['generatereport'])){
    require('../../assets/fpdf/fpdf.php');
    // Extend the FPDF class to create custom header and footer
    class PDF extends FPDF {
        // Page header
        function Header() {
            // Set font
            $this->SetFont('Arial', 'B', 12);
            // Title
            $this->Cell(0, 10, 'Appointment Report', 0, 1, 'C');
            // Line break
            $this->Ln(10);
        }

        // Page footer
        function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('Arial', 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' / {nb}', 0, 0, 'C');
        }
    }

    // Create a new PDF instance
    $pdf = new PDF();

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('Arial', '', 10);

    // Fetch appointment data and add to the PDF as a table
    $query = "SELECT * FROM appointment WHERE PID='$userid'";
    $result = $db->query($query);
    if ($result->num_rows > 0) {
        // Table header
        $pdf->Cell(10, 10, 'NO', 1, 0, 'C');
        $pdf->Cell(40, 10, 'DOCTOR NAME', 1, 0, 'C');
        $pdf->Cell(60, 10, 'SPECIALIZATION', 1, 0, 'C');
        $pdf->Cell(30, 10, 'FEES', 1, 0, 'C');
        $pdf->Cell(50, 10, 'APPOINTMENT DATE & TIME', 1, 1, 'C');

        // Table data
        $row_count = 1;
        while ($row = $result->fetch_assoc()) {
            $did=$row['DID'];
            $query1 = "SELECT * FROM doctor WHERE DID=$did";
            $result1 = $db->query($query1);
            $doctortb=$result1->fetch_assoc();
$sid=$row['SID'];
            $query2 = "SELECT * FROM specialization WHERE SID=$sid";
            $result2 = $db->query($query2);
            $specializationtb=$result2->fetch_assoc();
            $pdf->Cell(10, 10, $row_count++, 1, 0, 'C');
            $pdf->Cell(40, 10, $doctortb['DNAME'], 1, 0, 'C');
            $pdf->Cell(60, 10, $specializationtb['SPECIALIZATION'], 1, 0, 'C');
            $pdf->Cell(30, 10, $row['FEES'], 1, 0, 'C');
            $pdf->Cell(50, 10, $row['APPOINTMENT_DATE'] . '  ' . $row['APPOINTMENT_TIME'], 1, 1, 'C');
        }
    } else {
        $pdf->Cell(0, 10, 'No appointments found.', 0, 1);
    }

    // Output the PDF as a file (download)
    $pdf->Output('Patient_Appointment_Report.pdf', 'D');
}
?>