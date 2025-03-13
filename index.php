<?php
// donation_api.php - Simple backend API for saving donation form data

// Enable CORS to allow frontend to communicate with backend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include MongoDB PHP library via Composer's autoloader
require_once 'vendor/autoload.php';

// Connect to MongoDB
function connectToMongoDB() {
    try {
        // MongoDB connection string - modify as needed
        $uri = "mongodb+srv://hariusertesting:elqG88mMIsTjr690@db-life.0yvlo.mongodb.net/";
        $client = new MongoDB\Client($uri);
        
        // Select database and collection
        $database = $client->karunai_illam;
        $collection = $database->donations;
        
        return $collection;
    } catch (Exception $e) {
        return null;
    }
}

// Save donation data to database
function saveDonation($data) {
    $collection = connectToMongoDB();
    
    if (!$collection) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }
    
    // Generate a reference number
    $date = new DateTime();
    
    // Add reference number and timestamp to data
    $data['created_at'] = new MongoDB\BSON\UTCDateTime();
    
    // Insert donation data into MongoDB
    try {
        $result = $collection->insertOne($data);
        
        if ($result->getInsertedCount() > 0) {
            return [
                'success' => true,
                'message' => 'Donation submitted successfully',
               
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save donation'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Process only POST requests for donation submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'email', 'phone','company','address','country','state','city', 'donationFor', 'amount','reference'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missingFields)
        ]);
        exit;
    }
    
    // Save the donation data
    $result = saveDonation($data);
    echo json_encode($result);
} 
// GET method to verify API is working
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'status' => 'online',
        'message' => 'St. Antony\'s Karunai Illam Donation API'
    ]);
} 
// Handle other methods
else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>