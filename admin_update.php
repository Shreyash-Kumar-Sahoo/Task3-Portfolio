<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role_name'] !== 'Admin') {
    header("location: login.php");
    exit;
}
// admin_update.php
include('db_user.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request. User ID is missing.");
}

$user_id = intval($_GET['id']);
$page_title = "Edit User";
include('header.php');

$message = "";

// Fetch roles
$roles_query = "SELECT * FROM roles";
$roles_result = mysqli_query($conn_user, $roles_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = $_POST['role_id'];
    $new_password = $_POST['password']; 
    
    if (!empty($username) && !empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "<div class='alert error'>Error: Invalid email format.</div>";
        } else {
            if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username=?, email=?, password_hash=?, role_id=? WHERE id=?";
            $stmt = mysqli_prepare($conn_user, $sql);
            mysqli_stmt_bind_param($stmt, "sssii", $username, $email, $hashed_password, $role_id, $user_id);
        } else {
            $sql = "UPDATE users SET username=?, email=?, role_id=? WHERE id=?";
            $stmt = mysqli_prepare($conn_user, $sql);
            mysqli_stmt_bind_param($stmt, "ssii", $username, $email, $role_id, $user_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "<div class='alert success'>User updated successfully!</div>";
        } else {
            $message = "<div class='alert error'>Error: Could not execute query. " . mysqli_error($conn_user) . "</div>";
        }
        mysqli_stmt_close($stmt);
        }
    } else {
        $message = "<div class='alert error'>Username and Email are required fields.</div>";
    }
}

// Fetch current user data
$fetch_sql = "SELECT * FROM users WHERE id = ?";
$fetch_stmt = mysqli_prepare($conn_user, $fetch_sql);
mysqli_stmt_bind_param($fetch_stmt, "i", $user_id);
mysqli_stmt_execute($fetch_stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($fetch_stmt));
mysqli_stmt_close($fetch_stmt);

if (!$user_data) {
    die("User not found.");
}
?>
<main>
    <section id="contact">
        <h2>Edit User: <?php echo htmlspecialchars($user_data['username']); ?></h2>
        <?php echo $message; ?>

        <form action="admin_update.php?id=<?php echo $user_id; ?>" method="POST" id="contactForm">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email Address:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>New Password (Optional):</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">
            </div>

            <div class="form-group">
                <label>Role:</label>
                <select name="role_id">
                    <?php
                    if ($roles_result && mysqli_num_rows($roles_result) > 0) {
                        while ($row = mysqli_fetch_assoc($roles_result)) {
                            $selected = ($row['id'] == $user_data['role_id']) ? 'selected' : '';
                            echo "<option value='{$row['id']}' $selected>{$row['role_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <br>
            <div style="display: flex; gap: 15px; align-items: center; justify-content: flex-start; margin-top: 15px;">
                <button type="submit" id="submitBtn" style="width: auto; background-color: #d2b48c; color: #3e2723; padding: 12px 30px; border-radius: 25px; cursor: pointer; border: none; font-weight: bold;">Update User</button>
                <a href="admin.php" style="display: inline-block; padding: 10px 24px; border: 2px solid #5a3d31; border-radius: 25px; color: #5a3d31; text-decoration: none; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; background-color: transparent; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.backgroundColor='#5a3d31'; this.style.color='#fff';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#5a3d31';">Back to Dashboard</a>
            </div>
        </form>
    </section>
</main>
<?php include('footer.php'); ?>