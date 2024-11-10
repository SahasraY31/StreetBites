<?php
// Database configuration
include 'includes/conn.php';
// Initialize variables
$error = '';
$success = '';
$tagOptions = [];
$tagsQuery = "SELECT tag_name FROM tags";
$tagsResult = $conn->query($tagsQuery);
if ($tagsResult && $tagsResult->num_rows > 0) {
    while ($row = $tagsResult->fetch_assoc()) {
        $tagOptions[] = $row['tag_name'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    $isadmin = isset($_POST['isadmin']) ? 1 : 0;

    // User tags (for non-admins)
    $tags = isset($_POST['tags']) ? implode(',', $_POST['tags']) : null;

    // Admin-specific food truck info
    $foodtruck_name = $isadmin ? $_POST['foodtruck_name'] : null;
    $foodtruck_tags = $isadmin && isset($_POST['foodtruck_tags']) ? implode(',', $_POST['foodtruck_tags']) : null;

    // Insert user data into `userdata` table
    $stmt = $conn->prepare("INSERT INTO userdata (email, password, name, isadmin, tags, foodtruck_name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $email, $password, $name, $isadmin, $tags, $foodtruck_name);

    if ($stmt->execute()) {
        $success = "User registration successful!";

        // If user is admin, insert food truck info into `foodtruckinfo` table
        if ($isadmin) {
            $stmt_foodtruck = $conn->prepare("INSERT INTO foodtruckinfo (name, tags, des, current_location, logo, hours) VALUES (?, ?, '', '', '', '')");
            $stmt_foodtruck->bind_param("ss", $foodtruck_name, $foodtruck_tags);

            if ($stmt_foodtruck->execute()) {
                $success .= " Food truck info added successfully!";
            } else {
                $error = "Error adding food truck info: " . $stmt_foodtruck->error;
            }
            $stmt_foodtruck->close();
        }
    } else {
        $error = "Error: " . $stmt->error;
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://use.typekit.net/fxn5znb.css">
        <link rel="stylesheet" href="../css/signup.css">
        <title>Sign Up</title>
    </head>
<style>
    body { font-family: Arial, sans-serif; }
    .form-container { width: 300px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
    .form-container h2 { text-align: center; }
    .form-container label { display: block; margin-bottom: 5px; }
    .form-container input, .form-container button, .form-container select { width: 100%; margin-bottom: 10px; padding: 8px; }
    .form-container .admin-fields { display: none; }
    .success { color: green; }
    .error { color: red; }
</style>
<script>
    // Toggle fields based on admin selection
    function toggleAdminFields() {
        const isAdmin = document.getElementById('isadmin').checked;
        const userTags = document.getElementById('userTags');
        const adminFields = document.getElementById('adminFields');

        userTags.style.display = isAdmin ? 'none' : 'block';
        adminFields.style.display = isAdmin ? 'block' : 'none';
    }
</script>
</head>
<body>
    <a href="../index.html">
        <img src="../designs/streetbites_circle_logo.png" alt="streetbites Logo" class ="circle-logo" />
    </a>
    <div class="form-container">
        <h1>Sign Up</h1>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="name">Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="isadmin">Are you an admin?</label>
            <input type="checkbox" name="isadmin" id="isadmin" onclick="toggleAdminFields()">

            <!-- User tags (for non-admins only) -->
            <div id="userTags">
                <label for="tags">Select Tags (Hold Ctrl/Cmd to select multiple):</label>
                <select name="tags[]" id="tags" multiple>
                    <?php foreach ($tagOptions as $tag): ?>
                        <option value="<?= $tag ?>"><?= $tag ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Admin-specific food truck info -->
            <div id="adminFields" class="admin-fields">
                <h3>Food Truck Info</h3>

                <label for="foodtruck_name">Food Truck Name:</label>
                <input type="text" name="foodtruck_name" id="foodtruck_name">

                <label for="foodtruck_tags">Select Food Truck Tags (Hold Ctrl/Cmd to select multiple):</label>
                <select name="foodtruck_tags[]" id="foodtruck_tags" multiple>
                    <?php foreach ($tagOptions as $tag): ?>
                        <option value="<?= $tag ?>"><?= $tag ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>
