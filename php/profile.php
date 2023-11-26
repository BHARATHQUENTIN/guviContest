<?php

require_once(__DIR__ . "/../assets/vendor/autoload.php");
require_once(__DIR__ . "/../assets/vendor2/autoload.php");

use MongoDB\Client as MongoClient;

function connectToMongoDB()
{
    $mongoClient = new MongoClient("mongodb://localhost:27017");
    $database = $mongoClient->profile;
    $collection = $database->profile;
    return $collection;
}

function fetchUserData($userEmail)
{
    $response = ['success' => false];
    try {
        $collection = connectToMongoDB();
        $existingDocument = $collection->findOne(['email' => $userEmail]);

        if ($existingDocument) {
            $response['success'] = true;
            $response['userData'] = $existingDocument;
        } else {
            $response['error'] = 'User data not found in MongoDB.';
        }
    } catch (Exception $e) {
        $response['error'] = 'MongoDB Exception: ' . $e->getMessage();
    }

    return $response;
}

function insertOrUpdateUserData($userEmail, $name, $contact, $age, $dateOfBirth, $address)
{
    $response = ['success' => false];
    try {
        $collection = connectToMongoDB();

        $existingDocument = $collection->findOne(['email' => $userEmail]);

        // Create an array to store update data
        $updateData = [];

        if ($existingDocument) {
            // Always include the existing fields in the update data
            $updateData['contact'] = $existingDocument['contact'];
            $updateData['age'] = $existingDocument['age'];
            $updateData['dateOfBirth'] = $existingDocument['dateOfBirth'];
            $updateData['address'] = $existingDocument['address'];

            // Update with new values if provided
            if (isset($contact) && ($contact !== "NA" && $contact !== '')) {
                $updateData['contact'] = $contact;
            }

            // Calculate age based on date of birth
            if (isset($dateOfBirth) && $dateOfBirth !== "NA" && $dateOfBirth !== '') {
                $dob = new DateTime($dateOfBirth);
                $today = new DateTime();
                $age = $dob->diff($today)->y;
                $updateData['age'] = $age;
            }

            if (isset($dateOfBirth) && $dateOfBirth !== "NA" && $dateOfBirth !== '') {
                $updateData['dateOfBirth'] = $dateOfBirth;
            }

            if (isset($address) && $address !== "NA" && $address !== '') {
                $updateData['address'] = $address;
            }

            $updateResult = $collection->updateOne(
                ['email' => $userEmail],
                ['$set' => $updateData]
            );

            if ($updateResult->getModifiedCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Document updated in MongoDB successfully.';
                $response['age'] = $age; // Include calculated age in the response
            } else {
                $response['error'] = 'Failed to update document in MongoDB. No changes made.';
            }
        } else {
            // If the document doesn't exist, insert a new one
            $newDocument = [
                'email' => $userEmail,
                'firstname' => $name,
                'contact' => isset($contact) ? $contact : "NA",
                'age' => $age, // Use the provided age or the calculated age
                'dateOfBirth' => isset($dateOfBirth) ? $dateOfBirth : "NA",
                'address' => isset($address) ? $address : "NA",
            ];

            $result = $collection->insertOne($newDocument);

            if ($result->getInsertedCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Document inserted in MongoDB successfully.';
                $response['age'] = $age; // Include age in the response
            } else {
                $response['error'] = 'Failed to insert document in MongoDB.';
            }
        }
    } catch (Exception $e) {
        $response['error'] = 'MongoDB Exception: ' . $e->getMessage();
    }

    return $response;
}

if (isset($_POST['logout'])) {
    $redis = new Predis\Client();
    $redisKey = "user";
    $redis->del($redisKey);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

$redis = new Predis\Client();
$redisKey = "user";
$userEmail = $redis->hget($redisKey, 'emailAddress');
$name = $redis->hget($redisKey, 'name');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $response = fetchUserData($userEmail);
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = isset($_POST['contact']) ? $_POST['contact'] : "NA";
    $age = isset($_POST['age']) ? $_POST['age'] : "NA";
    $dateOfBirth = isset($_POST['dateOfBirth']) ? $_POST['dateOfBirth'] : "NA";
    $address = isset($_POST['address']) ? $_POST['address'] : "NA";

    if ($userEmail !== null) {
        if ($contact !== "NA" || $age !== "NA" || $dateOfBirth !== "NA" || $address !== "NA") {
            $response = insertOrUpdateUserData($userEmail, $name, $contact, $age, $dateOfBirth, $address);
        } else {
            $response = ['error' => 'No valid fields to update.'];
        }
    } else {
        $response = ['error' => 'User email not found in Redis.'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
