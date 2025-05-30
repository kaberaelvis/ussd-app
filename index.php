This is my ussd.php
<?php
// ussd.php

// DB Connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "ines_ruhengeri";
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Receive USSD Parameters
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];

// Break input text into steps
$input = explode("*", $text);
$level = count($input);

// Routing by level
switch ($level) {
    case 1:
        echo "CON 1. Enter your Registration Number";
        break;

    case 2:
        echo "CON 2. Enter your Password";
        break;

    case 3:
        // Validate Reg No and Password
        $regNo = $input[0];
        $pass = $input[1];
        $stmt = $conn->prepare("SELECT * FROM students WHERE Registration_number = ? AND Password = ?");
        $stmt->bind_param("ss", $regNo, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo "END Incorrect Registration Number or Password.";
        } else {
            // Get department list
            $departments = $conn->query("SELECT * FROM department");
            $menu = "CON 3. Choose Your Department\n";
            $i = 1;
            while ($dept = $departments->fetch_assoc()) {
                $menu .= "$i. " . $dept['Department_name'] . "\n";
                $deptMap[$i] = $dept['Department_id'];
                $deptNames[$i] = $dept['Department_name'];
                $i++;
            }
            file_put_contents("session/$sessionId.json", json_encode([
                "regNo" => $regNo,
                "deptMap" => $deptMap
            ]));
            echo $menu;
        }
        break;

    case 4:
        // Load session
        $sessionData = json_decode(file_get_contents("session/$sessionId.json"), true);
        $regNo = $sessionData['regNo'];
        $deptId = $sessionData['deptMap'][$input[2]] ?? null;

        // Check if selected dept matches student's dept
        $check = $conn->query("SELECT Department_id FROM students WHERE Registration_number = '$regNo'");
        $studentDept = $check->fetch_assoc()['Department_id'];
        if ($deptId != $studentDept) {
            echo "END You don't belong in this department. Choose your correct department.";
        } else {
            // Fetch courses in this department
            $courses = $conn->query("SELECT * FROM courses WHERE Department_id = '$deptId'");
            $menu = "CON 4. Choose Your Course\n";
            $i = 1;
            while ($course = $courses->fetch_assoc()) {
                $menu .= "$i. " . $course['course_name'] . "\n";
                $courseMap[$i] = $course['Course_id'];
                $courseNames[$i] = $course['course_name'];
                $i++;
            }
            $sessionData['deptId'] = $deptId;
            $sessionData['courseMap'] = $courseMap;
            $sessionData['courseNames'] = $courseNames;
            file_put_contents("session/$sessionId.json", json_encode($sessionData));
            echo $menu;
        }
        break;

    case 5:
        $sessionData = json_decode(file_get_contents("session/$sessionId.json"), true);
        $regNo = $sessionData['regNo'];
        $courseId = $sessionData['courseMap'][$input[3]] ?? null;
        $courseName = $sessionData['courseNames'][$input[3]] ?? "Unknown Course";

        $student = $conn->query("SELECT Student_name FROM students WHERE Registration_number = '$regNo'")->fetch_assoc();
        $marks = $conn->query("SELECT * FROM marks WHERE Registration_number = '$regNo' AND Course_id = '$courseId'")->fetch_assoc();

        if (!$marks) {
            echo "END No results found for selected course.";
        } else {
            $status = ($marks['Final_marks'] > 10) ? "Passed" : "Failed";

            echo "END Hello {$student['Student_name']} in $courseName,\n";
            echo "You have received:\n";
            echo "- Quiz: {$marks['Quiz']}\n";
            echo "- CAT: {$marks['CAT']}\n";
            echo "- Final Exam: {$marks['Final_exam']}\n";
            echo "- Grand Total: {$marks['Grand_Total']}\n";
            echo "- Final Marks: {$marks['Final_marks']}\n";
            echo "You have: $status";
        }
        break;

    default:
        echo "END Invalid input. Please try again.";
        break;
}

$conn->close();
?>


