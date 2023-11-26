<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'guvi');
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if (mysqli_connect_errno()) {
    printf('', mysqli_connect_error());
    exit(1);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fName = $_POST["firstName"];
    $lName = $_POST["lastName"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $pcm = $_POST["passwordConfirm"];

   
    if ($password != $pcm) {
        $response = array("error" => "Passwords do not match", "errorCode" => 100);
        echo json_encode($response);
        exit();
    }

    
    $checkEmail= "SELECT id FROM user WHERE emailAddress = ?";
    $check = $db->prepare($checkEmail);
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $response = array("error" => "Email already registered", "errorCode" => 200);
        echo json_encode($response);
        exit();
    }

  
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertQuery = "INSERT INTO user (firstName, lastName, emailAddress, password) VALUES (?, ?, ?, ?)";
    $insert = $db->prepare($insertQuery);
    $insert->bind_param("ssss", $fName, $lName, $email, $hashedPassword);

    if ($insert->execute()) {
        $userId = $insert->insert_id;
        echo json_encode(array("success" => true, "userId" => $userId));
    } else {
        $response = array("error" => "Database error", "errorCode" => 300);
        echo json_encode($response);
    }

    $check->close();
    $insert->close();
    $db->close();
}
?>
