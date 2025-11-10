<?php
// define JSON headers
header('Content-Type: application/json; charset=utf-8'); // Set content type to JSON
header('Access-Control-Allow-Origin: *'); // Allow access from any origin
header('Access-Control-Allow-Methods: POST'); // Allow POST method and not GET, PUT, DELETE, OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Origin, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // Allow specific headers

// require database connection
require_once '../inc/pdo.php';

// Verify that the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = [
        'status'=> http_response_code(405), // Method Not Allowed
        'message'=> 'Method Not Allowed. Only POST method is allowed'
    ];
    echo json_encode($response);
    exit;
}

// get the raw POST data
$requestBody = file_get_contents('php://input'); // get the JSON raw POST data

// decode the JSON data
$JSONData = json_decode($requestBody, true);

// retreive data from a form-data submission
$FORMData = [
    'fullname'=> trim($_POST['fullname'] ?? ''),
    'email'=> trim($_POST['email'] ?? '')
];

// determine which data to use: JSON or form-data (JSON takes precedence)
if(!empty($JSONData)) {
    $StudentData = $JSONData;
} else {
    $StudentData = $FORMData;
}

// validate required fields
if(empty($StudentData['fullname']) || empty($StudentData['email'])) {
    $response = [
        'status'=> http_response_code(400), // Bad Request
        'message'=> 'Missing required input. FullName and email are required.'
    ];
    echo json_encode($response);
    exit;
}

// sanitize input data
$StudentData['fullname'] = addslashes($StudentData['fullname']);
$StudentData['email'] = filter_var($StudentData['email'], FILTER_SANITIZE_EMAIL);

// validate email format
if (!filter_var($StudentData['email'], FILTER_VALIDATE_EMAIL)) {
    $response = [
        'status'=> http_response_code(400), // Bad Request
        'message'=> 'Invalid email format.'
    ];
    echo json_encode($response);
    exit;
}

// prepare and execute the insert statement
try {
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email) VALUES (:fullname, :email)");
    $stmt->bindParam(":fullname", $StudentData['fullname'], PDO::PARAM_STR);
    $stmt->bindParam(":email", $StudentData['email'], PDO::PARAM_STR);

    if($stmt->execute()){
        $response = [
            'status'=> http_response_code(201), // Created
            'message'=> 'Student created successfully.',
            'data'=> [
                'userId'=> $pdo->lastInsertId(),
                'fullname'=> $StudentData['fullname'],
                'email'=> $StudentData['email']
            ]
        ];
        echo json_encode($response);
        exit;
    } else {
        $response = [
            'status'=> http_response_code(500), // Internal Server Error
            'message'=> 'Failed to create Student.'
        ];
        echo json_encode($response);
        exit;
    }
} catch (Exception $e) {
    $response = [
        'status'=> http_response_code(500), // Internal Server Error
        'message'=> 'An error occurred: ' . $e->getMessage()
    ];
    echo json_encode($response);
    exit;
}
