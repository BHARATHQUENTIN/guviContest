<?php

require_once(__DIR__ . "/../assets/vendor/autoload.php");
use Predis\Client;

$redis = new Client();


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
    $userEmail = $_POST['email'];
    $userPassword = $_POST['password'];

    // Use a single query to check both email and password
    $query = $db->prepare("SELECT id, firstName, emailAddress, password FROM user WHERE emailAddress=?");
    $query->bind_param("s", $userEmail);
    $query->execute();
    $query->store_result();
    
    if ($query->num_rows == 1) {
        // Fetch user details
        $query->bind_result($userId, $userName, $userEmail, $hashedPassword);
        $query->fetch();
    
        // Verify the password
        if (password_verify($userPassword, $hashedPassword)) {
            // Store user information in Redis
            $redis = new Predis\Client();
            $redisKey = "user";
            $redis->hmset($redisKey, 'emailAddress', $userEmail, 'name', $userName);
    
            // Return user details as JSON response
            echo json_encode(['id' => $userId, 'fisrtName' => $userName, 'emailAddress' => $userEmail]);
        } else {
            echo "Wrong email or password";
        }
    } else {
        echo "Wrong email or password";
    }
    
    $query->close();
}
?>
