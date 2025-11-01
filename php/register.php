<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();
include '../includes/db_connect.php';
include '../includes/csrf.php';

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    validate_csrf_token();
    // Sanitize and validate inputs
    $id_number = trim($_POST['id_number']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $name_extension = trim($_POST['name_extension']);
    $birth_date = $_POST['birth_date'];
    $address = trim($_POST['address']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $auth_q1 = $_POST['auth_q1'];
    $auth_a1 = trim($_POST['auth_a1']);
    $auth_q2 = $_POST['auth_q2'];
    $auth_a2 = trim($_POST['auth_a2']);
    $auth_q3 = $_POST['auth_q3'];
    $auth_a3 = trim($_POST['auth_a3']);

    // --- Comprehensive Server-Side Validation ---

    // Name validation function
    function validate_name($name, $field_name) {
        if (empty($name)) return null;
        if (!preg_match('/^[a-zA-Z\s.-]+$/', $name)) return "$field_name can only contain letters, spaces, dots, and hyphens.";
        if (preg_match('/\s{2,}/', $name)) return "$field_name cannot contain double spaces.";
        if (preg_match('/([a-zA-Z])\\1\\1/i', $name)) return "$field_name cannot contain three consecutive identical letters.";
        if (strtoupper($name) === $name && strlen($name) > 1) return "$field_name cannot be all uppercase.";
        return null;
    }

    // Validate required names
    $name_fields = ['first_name' => 'First Name', 'last_name' => 'Last Name'];
    foreach ($name_fields as $field => $label) {
        if (empty($$field)) {
            $errors[] = "$label is required.";
        } elseif ($error = validate_name($$field, $label)) {
            $errors[] = $error;
        }
    }

    // Validate optional names
    $optional_name_fields = ['middle_name' => 'Middle Name', 'name_extension' => 'Name Extension'];
    foreach ($optional_name_fields as $field => $label) {
        if (!empty($$field) && ($error = validate_name($$field, $label))) {
            $errors[] = $error;
        }
    }

    // ID number format
    if (!preg_match('/^\d{4}-\d{4}$/', $id_number)) {
        $errors[] = "ID Number must be in the format xxxx-xxxx.";
    }

    // Address validation (basic check for structure)
    if (count(explode(',', $address)) < 6) {
        $errors[] = "Address format must be: Purok/Street, Barangay, City/Municipal, Province, Country, ZIP code.";
    }

    // Password strength
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must be at least 8 characters long and include uppercase, lowercase, and numeric characters.";
    }

    // Password match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Age calculation
    $age = date_diff(date_create($birth_date), date_create('now'))->y;
    if ($age < 18) {
        $errors[] = "You must be at least 18 years old to register.";
    }

    // Check for duplicate ID number
    $stmt = $conn->prepare("SELECT id_number FROM users WHERE id_number = ?");
    $stmt->bind_param("s", $id_number);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "ID Number already exists.";
    }
    $stmt->close();

    // Check for duplicate username
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username already exists.";
    }
    $stmt->close();

    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $stmt = $conn->prepare("INSERT INTO users (id_number, first_name, middle_name, last_name, name_extension, birth_date, age, address, username, password, auth_q1, auth_a1, auth_q2, auth_a2, auth_q3, auth_a3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssisssssssss", $id_number, $first_name, $middle_name, $last_name, $name_extension, $birth_date, $age, $address, $username, $hashed_password, $auth_q1, $auth_a1, $auth_q2, $auth_a2, $auth_q3, $auth_a3);

        if ($stmt->execute()) {
            $success_message = "Registration successful! You can now <a href='login.php'>log in</a>.";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Secure User System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="form-container">
            <h2>Create your account</h2>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="success">
                    <p><?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>
            <form action="register.php" method="POST" id="register-form">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="form-group">
                    <label for="id_number">ID Number <span class="required">*</span></label>
                    <input type="text" id="id_number" name="id_number" placeholder="xxxx-xxxx" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name <span class="optional">optional</span></label>
                    <input type="text" id="middle_name" name="middle_name">
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="name_extension">Name Extension <span class="optional">optional</span></label>
                    <input type="text" id="name_extension" name="name_extension">
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="birth_date">Birthdate <span class="required">*</span></label>
                    <input type="date" id="birth_date" name="birth_date" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="text" id="age" name="age" disabled>
                </div>
                <div class="form-group">
                    <label for="address">Address <span class="required">*</span></label>
                    <input type="text" id="address" name="address" placeholder="Purok/Street, Barangay, City/Municipal, Province, Country, ZIP code" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-strength"></div>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <div class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="auth_q1">Authentication Question 1 <span class="required">*</span></label>
                    <select id="auth_q1" name="auth_q1" required>
                        <option value="Who is your best friend in elementary?">Who is your best friend in elementary?</option>
                        <option value="What is the name of your favorite pet?">What is the name of your favorite pet?</option>
                        <option value="Who is your favorite teacher in high school?">Who is your favorite teacher in high school?</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="auth_a1">Answer 1 <span class="required">*</span></label>
                    <input type="text" id="auth_a1" name="auth_a1" required>
                </div>
                <div class="form-group">
                    <label for="auth_q2">Authentication Question 2 <span class="required">*</span></label>
                    <select id="auth_q2" name="auth_q2" required>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What was the name of your first school?">What was the name of your first school?</option>
                        <option value="In what city were you born?">In what city were you born?</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="auth_a2">Answer 2 <span class="required">*</span></label>
                    <input type="text" id="auth_a2" name="auth_a2" required>
                </div>
                <div class="form-group">
                    <label for="auth_q3">Authentication Question 3 <span class="required">*</span></label>
                    <select id="auth_q3" name="auth_q3" required>
                        <option value="What is your favorite movie?">What is your favorite movie?</option>
                        <option value="What is your favorite food?">What is your favorite food?</option>
                        <option value="What is your favorite color?">What is your favorite color?</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="auth_a3">Answer 3 <span class="required">*</span></label>
                    <input type="text" id="auth_a3" name="auth_a3" required>
                </div>
                <button type="submit" class="btn">Register</button>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="../js/validation.js"></script>
</body>
</html>