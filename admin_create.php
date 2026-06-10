<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role_name'] !== 'Admin') {
    header("location: login.php");
    exit;
}
// admin_create.php
include('db_user.php');
$page_title = "Add New User";
include('header.php');

$message = "";

// Fetch roles for the dropdown
$roles_query = "SELECT * FROM roles";
$roles_result = mysqli_query($conn_user, $roles_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role_id = $_POST['role_id'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "<div class='alert error'>Error: Invalid email format.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password_hash, role_id) VALUES (?, ?, ?, ?)";
    
            if ($stmt = mysqli_prepare($conn_user, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $hashed_password, $role_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "<div class='alert success'>User created successfully!</div>";
                } else {
                    $message = "<div class='alert error'>Error: Could not execute query. " . mysqli_error($conn_user) . "</div>";
                }
                mysqli_stmt_close($stmt);
            }
        }
    } else {
        $message = "<div class='alert error'>Please fill in all required fields.</div>";
    }
}
?>
<main>
    <section id="contact">
        <h2>Add New User</h2>
        <?php echo $message; ?>
        
        <form action="admin_create.php" method="POST" id="contactForm">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email Address:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Role:</label>
                <select name="role_id">
                    <?php
                    if ($roles_result && mysqli_num_rows($roles_result) > 0) {
                        while ($row = mysqli_fetch_assoc($roles_result)) {
                            $selected = ($row['role_name'] == 'User') ? 'selected' : '';
                            echo "<option value='{$row['id']}' $selected>{$row['role_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <br>
            <div style="display: flex; gap: 15px; align-items: center; justify-content: flex-start; margin-top: 15px;">
                <button type="submit" id="submitBtn" style="width: auto; padding: 12px 30px; border-radius: 25px; cursor: pointer;">Create User</button>
                <a href="admin.php" style="display: inline-block; padding: 10px 24px; border: 2px solid #5a3d31; border-radius: 25px; color: #5a3d31; text-decoration: none; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; background-color: transparent; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='#5a3d31'; this.style.color='#fff';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#5a3d31';">Back to Dashboard</a>
            </div>
        </form>
    </section>
</main>
<?php include('footer.php'); ?>