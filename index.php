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
        $uri = "mongodb+srv://dbkarunai:76qIFZyTwJk5cbx6@donation.g5ps9.mongodb.net/?retryWrites=true&w=majority&appName=donation";
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

// Get all donations from database
function getAllDonations() {
    $collection = connectToMongoDB();
    
    if (!$collection) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }
    
    try {
        // Find all documents in the collection
        $cursor = $collection->find([], [
            'sort' => ['created_at' => -1] // Sort by creation date, newest first
        ]);
        
        $donations = [];
        foreach ($cursor as $document) {
            // Convert MongoDB\BSON\UTCDateTime to readable format
            if (isset($document['created_at']) && $document['created_at'] instanceof MongoDB\BSON\UTCDateTime) {
                $document['created_at'] = $document['created_at']->toDateTime()->format('Y-m-d H:i:s');
            }
            
            // Convert MongoDB ObjectId to string
            $document['_id'] = (string) $document['_id'];
            
            $donations[] = $document;
        }
        
        return [
            'success' => true,
            'data' => $donations
        ];
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
// GET method for API status or to retrieve all donations
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the request is specifically for getting all donations
    if (isset($_GET['action']) && $_GET['action'] === 'getAllDonations') {
        $result = getAllDonations();
        echo json_encode($result);
    } else {
        // Default API status response
        echo json_encode([
            'status' => 'online',
            'message' => 'St. Antony\'s Karunai Illam Donation API'
        ]);
    }
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
