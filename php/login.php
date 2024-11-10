<?php
include 'includes/conn.php';

session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    if($_SESSION['admin'] == 0){
        header("Location: home.php");
        exit();
    } else {
        header("Location: adminhome.php");
        exit();
    }
    
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = $name = $password = '';
$havePost = false; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $havePost = true;

    $email = htmlspecialchars(trim($_POST["email"]));
    $password = htmlspecialchars(trim($_POST["password"]));

    // Validation
    if (empty($email)) {
        $errors .= '<li>Email may not be blank</li>';
        $focusId = '#email';
    }
    if (empty($password)) {
        $errors .= '<li>Password may not be blank</li>';
        if (empty($focusId)) $focusId = '#password';
    }

    // If no errors, process login
    if (empty($errors)) {
        $encryptedPassword = sha1($password);
        $stmt = $conn->prepare("SELECT * FROM userdata WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $encryptedPassword);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<div class='messages'><h4>Login successful! Redirecting...</h4></div>";

            $_SESSION['id'] = $user['id']; 
            $_SESSION['name'] = $user['name'];
            $_SESSION['foodtruck_name'] = $user['foodtruck_name'];
            $_SESSION['isadmin'] = $user['isadmin'];
   
            if($_SESSION['admin'] == 1){
                $stmt = $conn->prepare("SELECT * FROM foodtruckinfo WHERE foodtruck_name = ?");
                $stmt->bind_param("s", $_SESSION['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                // if ($result->num_rows > 0){
                //     $_SESSION['club_filled'] = 1;
                //     header("Location: ../admin/home.php");
                //     exit();
                // } else {
                //     $_SESSION['club_filled'] = 0;
                //     header("Location: ../admin/clubInfo.php");
                //     exit();
                // }
                
            } else{
                header("Location: home.php");
                exit();
            }
            
        } else {
            $errors .= '<li>Invalid credentials. Please try again.</li>';
            $focusId = '#name';
        }

        
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>StreetBites - Login</title>
   <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
        <a href="../index.html">
            <img src="../designs/streetbites_circle_logo.png" alt="streetbites Logo" class ="circle-logo" />
        </a>
   <div class="login-container">

      <header>

      </header>

      <?php
        // Display errors if any
        if (!empty($errors)) {
            echo '<div class="messages"><h4>Please correct the following errors:</h4><ul>' . $errors . '</ul></div>';
        }
      ?>

      <form action="#" method="POST">
         <h1>Welcome Back</h1>

         <div class="form-main">
            <input type="email" id="email" name="email" placeholder="Email" /><br /><br />

            <input type="password" id="password" name="password" placeholder="Password" /><br /><br /><br />

            <input type="submit" value="Log In" />
            <p id="notyet"> Don't have an account? <a href="signup.php">Sign up</a></p>
         </div>


      </form>

   </div>


   <script>
   // Automatically focus on the first field that needs correction
   <?php if (!empty($focusId)) { ?>
   document.querySelector("<?php echo $focusId; ?>").focus();
   <?php } ?>
   </script>
</body>

</html>