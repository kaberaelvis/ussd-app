<?php
require_once 'config.php';
require_once 'database.php';

header('Content-Type: text/plain');

// Initialize database connection
$db = new Database();

// Get request parameters
$text = isset($_REQUEST['text']) ? $_REQUEST['text'] : "";
$sessionId = isset($_REQUEST['sessionId']) ? $_REQUEST['sessionId'] : "";
$phoneNumber = isset($_REQUEST['phoneNumber']) ? $_REQUEST['phoneNumber'] : "";

// Split text into levels
$levels = explode("*", $text);
$currentLevel = count($levels) - 1;

// Initialize session data
$sessionData = [];

// Check if session exists
if ($sessionId) {
    // In a real application, you would store session data in a database
    // For now, we'll use a simple array
    if (isset($_SESSION[$sessionId])) {
        $sessionData = $_SESSION[$sessionId];
    }
}

// Handle different levels
switch($currentLevel) {
    case 0: // Enter Registration Number
        $response = "CON 1. Enter your Registration Number\n";
        break;
        
    case 1: // Validate Registration Number
        $regNumber = $db->escape($levels[1]);
        $sql = "SELECT * FROM students WHERE Registration_number = '$regNumber'";
        $result = $db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $sessionData['reg_number'] = $regNumber;
            $response = "CON 2. Enter your Password\n";
        } else {
            $response = "END Incorrect Registration Number.";
        }
        break;
        
    case 2: // Validate Password
        $password = $db->escape($levels[2]);
        $sql = "SELECT * FROM students WHERE Registration_number = '{$sessionData['reg_number']}' AND Password = '$password'";
        $result = $db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $sessionData['student_id'] = $result->fetch_assoc()['Student_id'];
            
            // Get departments
            $sql = "SELECT Department_id, Department_name FROM department";
            $result = $db->query($sql);
            
            $response = "CON 3. Choose Your Department\n";
            $i = 1;
            while($row = $result->fetch_assoc()) {
                $response .= "$i. {$row['Department_name']}\n";
                $sessionData['departments'][$i] = $row['Department_id'];
                $i++;
            }
        } else {
            $response = "END Incorrect Password.";
        }
        break;
        
    case 3: // Validate Department
        $deptChoice = intval($levels[3]);
        if (isset($sessionData['departments'][$deptChoice])) {
            $deptId = $sessionData['departments'][$deptChoice];
            
            // Get student's department
            $sql = "SELECT Department_id FROM students WHERE Student_id = '{$sessionData['student_id']}'";
            $result = $db->query($sql);
            $studentDept = $result->fetch_assoc()['Department_id'];
            
            if ($deptId == $studentDept) {
                // Get courses for this department
                $sql = "SELECT Course_id, Course_name FROM courses WHERE Department_id = $deptId";
                $result = $db->query($sql);
                
                $response = "CON 4. Choose Your Course\n";
                $i = 1;
                while($row = $result->fetch_assoc()) {
                    $response .= "$i. {$row['Course_name']}\n";
                    $sessionData['courses'][$i] = $row['Course_id'];
                    $i++;
                }
            } else {
                $response = "END You don't belong in this department. Choose your correct department.";
            }
        } else {
            $response = "END Invalid department selection.";
        }
        break;
        
    case 4: // Show Results
        $courseChoice = intval($levels[4]);
        if (isset($sessionData['courses'][$courseChoice])) {
            $courseId = $sessionData['courses'][$courseChoice];
            
            // Get student's details
            $sql = "SELECT Student_name FROM students WHERE Student_id = '{$sessionData['student_id']}'";
            $result = $db->query($sql);
            $studentName = $result->fetch_assoc()['Student_name'];
            
            // Get course name
            $sql = "SELECT Course_name FROM courses WHERE Course_id = $courseId";
            $result = $db->query($sql);
            $courseName = $result->fetch_assoc()['Course_name'];
            
            // Get marks
            $sql = "SELECT * FROM marks WHERE Registration_number = '{$sessionData['reg_number']}' AND Course_id = $courseId";
            $result = $db->query($sql);
            $marks = $result->fetch_assoc();
            
            // Calculate final marks
            $finalMarks = $marks['Quiz'] + $marks['CAT'] + $marks['Final_exam'];
            
            // Determine pass/fail
            $status = ($finalMarks > 10) ? "Passed" : "Failed";
            
            $response = "END Hello $studentName in $courseName,\n";
            $response .= "You have received:\n";
            $response .= "- Quiz: {$marks['Quiz']}\n";
            $response .= "- CAT: {$marks['CAT']}\n";
            $response .= "- Final Exam: {$marks['Final_exam']}\n";
            $response .= "- Grand Total: $finalMarks\n";
            $response .= "- Final Marks: $finalMarks\n\n";
            $response .= "You have: $status";
        } else {
            $response = "END Invalid course selection.";
        }
        break;
}

// Store session data
$_SESSION[$sessionId] = $sessionData;

echo $response;
$db->close();
?>
