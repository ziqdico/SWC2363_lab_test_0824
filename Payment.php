<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Payslip</title>
</head>
<body>

<?php
// Database connection
$servername = "localhost";
$username = "root";      // Your MySQL username
$password = "";          // Your MySQL password
$dbname = "employee_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from form
    $employeeName = $_POST['employeeName'];
    $staffID = $_POST['staffID'];
    $month = $_POST['month'];
    $position = $_POST['position'];
    $deduction = $_POST['deduction'];

    // Define salary based on position
    $salaryRates = [
        "Clerk 1" => 1700,
        "Clerk 2" => 2000,
        "Driver" => 2500,
        "Temp staff" => 1200
    ];
    $salary = isset($salaryRates[$position]) ? $salaryRates[$position] : 0;

    // Calculate deductions
    $kwspRate = 0.12;
    $socsoRate = 0.05;
    $kwspAmount = ($deduction == "Yes") ? $salary * $kwspRate : 0;
    $socsoAmount = ($deduction == "Yes") ? $salary * $socsoRate : 0;
    $netSalary = $salary - ($kwspAmount + $socsoAmount);
    $paymentDate = date("Y-m-d");

    // Check if employee exists, if not, add them to employees table
    $checkEmployee = "SELECT * FROM employees WHERE staffID = ?";
    $stmt = $conn->prepare($checkEmployee);
    $stmt->bind_param("s", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert new employee
        $insertEmployee = "INSERT INTO employees (staffID, employeeName, position) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertEmployee);
        $stmt->bind_param("sss", $staffID, $employeeName, $position);
        $stmt->execute();
    }

    // Insert the payslip record
    $insertPayslip = "INSERT INTO payslips (staffID, month, paymentDate, basicSalary, kwspAmount, socsoAmount, netSalary)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertPayslip);
    $stmt->bind_param("sssdddd", $staffID, $month, $paymentDate, $salary, $kwspAmount, $socsoAmount, $netSalary);
    $stmt->execute();

    // Display the payslip
    echo "<h1>Payslip</h1>";
    echo "<p><strong>Date of Payment:</strong> " . $paymentDate . "</p>";
    echo "<p><strong>Employee Name:</strong> $employeeName</p>";
    echo "<p><strong>Staff ID:</strong> $staffID</p>";
    echo "<p><strong>Month of Payment:</strong> $month</p>";
    echo "<p><strong>Position:</strong> $position</p>";
    echo "<p><strong>Basic Salary:</strong> RM $salary</p>";
    echo "<p><strong>KWSP Deduction:</strong> RM $kwspAmount</p>";
    echo "<p><strong>SOCSO Deduction:</strong> RM $socsoAmount</p>";
    echo "<p><strong>Net Salary:</strong> RM $netSalary</p>";

    // Print button
    echo '<button onclick="window.print()">Print Payslip</button>';

    $stmt->close();
}

$conn->close();
?>

</body>
</html>
