<?php 
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

include('db_user.php'); 
$page_title = "Edit Profile";

$user_id = $_SESSION['user_id'];
$status_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = $_POST['password']; 
    
    if (!empty($username) && !empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $status_message = "<div class='alert error'>Error: Invalid email format.</div>";
        } else {
            // Check if email belongs to someone else
            $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
            if ($stmt_check = mysqli_prepare($conn_user, $check_email)) {
                mysqli_stmt_bind_param($stmt_check, "si", $email, $user_id);
                mysqli_stmt_execute($stmt_check);
                mysqli_stmt_store_result($stmt_check);
                if (mysqli_stmt_num_rows($stmt_check) > 0) {
                    $status_message = "<div class='alert error'>Error: Email already exists.</div>";
                }
                mysqli_stmt_close($stmt_check);
            }

            if (empty($status_message)) {
                $update_sql = "UPDATE users SET username=?, email=? WHERE id=?";
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET username=?, email=?, password_hash=? WHERE id=?";
                }

                // Handle file upload
                $profile_picture = "";
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
                    $file_tmp_path = $_FILES['profile_picture']['tmp_name'];
                    $file_name = $_FILES['profile_picture']['name'];
                    $file_size = $_FILES['profile_picture']['size'];
                    $file_type = $_FILES['profile_picture']['type'];
                    $fileNameCmps = explode(".", $file_name);
                    $file_extension = strtolower(end($fileNameCmps));
                    
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $max_size = 2 * 1024 * 1024; // 2MB

                    if (in_array($file_extension, $allowed_extensions)) {
                        if ($file_size <= $max_size) {
                            $new_file_name = md5(time() . $file_name) . '.' . $file_extension;
                            $upload_file_dir = './uploads/';
                            $dest_path = $upload_file_dir . $new_file_name;
                            
                            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                                $profile_picture = $new_file_name;
                                // Add to SQL query
                                if (!empty($new_password)) {
                                    $update_sql = "UPDATE users SET username=?, email=?, password_hash=?, profile_picture=? WHERE id=?";
                                } else {
                                    $update_sql = "UPDATE users SET username=?, email=?, profile_picture=? WHERE id=?";
                                }
                            } else {
                                $status_message = "<div class='alert error'>Error moving uploaded file.</div>";
                            }
                        } else {
                            $status_message = "<div class='alert error'>Error: Profile picture exceeds 2MB limit.</div>";
                        }
                    } else {
                        $status_message = "<div class='alert error'>Error: Only JPG, JPEG, PNG, and GIF files are allowed.</div>";
                    }
                }

                if (empty($status_message)) {
                    $stmt = mysqli_prepare($conn_user, $update_sql);
                    if (!empty($new_password) && !empty($profile_picture)) {
                        mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $hashed_password, $profile_picture, $user_id);
                    } else if (!empty($new_password) && empty($profile_picture)) {
                        mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $hashed_password, $user_id);
                    } else if (empty($new_password) && !empty($profile_picture)) {
                        mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $profile_picture, $user_id);
                    } else {
                        mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);
                    }

                    if (mysqli_stmt_execute($stmt)) {
                        $status_message = "<div class='alert success'>Profile updated successfully!</div>";
                        $_SESSION['username'] = $username; // Update session
                        if (!empty($profile_picture)) {
                            $_SESSION['profile_picture'] = $profile_picture;
                        }
                    } else {
                        $status_message = "<div class='alert error'>Error updating profile.</div>";
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } else {
        $status_message = "<div class='alert error'>Username and Email are required.</div>";
    }
}

// Fetch current user info
$query = "SELECT username, email, profile_picture FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn_user, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

include('header.php'); 
?>

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        margin: 0;
    }
    .form-container {
        background: #fff;
        padding: 50px 40px;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        max-width: 550px;
        margin: 60px auto;
        position: relative;
        z-index: 1;
        border: none;
    }
    .form-container h2 {
        text-align: center;
        margin-bottom: 35px;
        color: #333;
        font-family: 'Inter', sans-serif;
        font-weight: 800;
        font-size: 2.2rem;
    }
    .form-group {
        margin-bottom: 25px;
    }
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #555;
        font-size: 0.95rem;
    }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"],
    .form-group input[type="file"] {
        width: 100%;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 12px;
        font-size: 15px;
        box-sizing: border-box;
        background: #fdfdfd;
        font-family: 'Inter', sans-serif;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .form-group input:focus {
        border-color: #4e73df;
        box-shadow: 0 0 20px rgba(78, 115, 223, 0.15);
        background: #fff;
        outline: none;
        transform: translateY(-2px);
    }
    .btn-submit {
        background: #4e73df;
        color: white;
        padding: 16px 20px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        font-weight: 800;
        font-family: 'Inter', sans-serif;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        margin-top: 15px;
    }
    .btn-submit:hover {
        background: #2e59d9;
        transform: translateY(-4px);
        box-shadow: 0 15px 30px rgba(78, 115, 223, 0.3);
    }
    .alert {
        padding: 16px;
        border-radius: 10px;
        margin-bottom: 25px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        text-align: center;
    }
    .alert.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .alert.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .profile-img-preview {
        display: block;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 35px auto;
        border: 4px solid #4e73df;
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .profile-img-preview:hover {
        transform: scale(1.08) rotate(3deg);
    }
    .header-container {
        position: relative;
        z-index: 10;
    }
</style>

<div class="form-container">
    <h2>Edit Profile</h2>
    <?php echo $status_message; ?>

    <?php 
    // Determine profile picture source
    $pic_src = "uploads/default.png";
    if (!empty($user['profile_picture'])) {
        // Support both old external links and new uploaded files
        if (filter_var($user['profile_picture'], FILTER_VALIDATE_URL)) {
            $pic_src = $user['profile_picture'];
        } else {
            $pic_src = "uploads/" . $user['profile_picture'];
        }
    }
    ?>
    <img src="<?php echo htmlspecialchars($pic_src); ?>" alt="Profile Picture" class="profile-img-preview" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=random';">

    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" placeholder="Enter new password">
        </div>

        <div class="form-group">
            <label for="profile_picture">Upload New Profile Picture (Max 2MB)</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg, image/png, image/gif">
        </div>

        <button type="submit" class="btn-submit">Update Profile</button>
    </form>
</div>

<?php include('footer.php'); ?>