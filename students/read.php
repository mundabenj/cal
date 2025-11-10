<?php
// define JSON headers
header('Content-Type: application/json; charset=utf-8'); // Set content type to JSON
header('Access-Control-Allow-Origin: *'); // Allow access from any origin
header('Access-Control-Allow-Methods: GET'); // Allow GET method and not POST, PUT, DELETE, OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Origin, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // Allow specific headers

// require database connection
require_once '../inc/pdo.php';

// Verify that the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response = [
        'status'=> http_response_code(405), // Method Not Allowed
        'message'=> 'Method Not Allowed. Only GET method is allowed'
    ];
    echo json_encode($response);
    exit;
}

// use if to fetch  a single student by userId or all students
if(isset($_GET['userId']) && is_numeric($_GET['userId']) && $_GET['userId'] != '') {
    // fetch a single student
    $userId = intval($_GET['userId']);

    try{
        // prepare the SQL statement
        $stmt = $pdo->prepare("SELECT * FROM users WHERE userId = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT); // bind the userId parameter
        $stmt->execute(); // execute the statement

        // Count number of rows returned
        $rowCount = $stmt->rowCount();
        if($rowCount > 0) {
            $studentData = $stmt->fetch(PDO::FETCH_ASSOC); // fetch single student data
            $response = [
                'status'=> http_response_code(200), // OK
                'message'=> 'Single student fetched successfully.',
                'data'=> $studentData
            ];
            echo json_encode($response);
            exit;
        } else {
            $response = [
                'status'=> http_response_code(404), // Not Found
                'message'=> 'Student not found.'
            ];
            echo json_encode($response);
            exit;
        }
    }  catch (PDOException $e) {
        $response = [
            'status'=> http_response_code(500), // Internal Server Error
            'message'=> 'An error occurred while fetching student data.' . $e->getMessage()
        ];
        echo json_encode($response);
        exit;
    }
}else {
    // fetch all students

    try{
        $stmt = $pdo->prepare('SELECT * FROM users'); // prepare the SQL statement
        $stmt->execute(); // execute the statement

        $rowCount = $stmt->rowCount();
        if($rowCount > 0) {
            $studentData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'status'=> http_response_code(200), // OK
                'message'=> 'All students fetched successfully.',
                'data'=> $studentData
            ];
            echo json_encode($response);
            exit;
        } else {
            $response = [
                'status'=> http_response_code(404), // Not Found
                'message'=> 'No students found.'
            ];
            echo json_encode($response);
            exit;
        }
    }  catch (PDOException $e) {
        $response = [
            'status'=> http_response_code(500), // Internal Server Error
            'message'=> 'An error occurred while fetching student data.' . $e->getMessage()
        ];
        echo json_encode($response);
        exit;
    }
}