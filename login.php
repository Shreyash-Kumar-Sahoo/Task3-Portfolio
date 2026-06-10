<?php 
    session_start();
    $page_title = "Login - Task 2";
    $use_bootstrap = true;
    include('db_user.php');
    
    $message = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        if (!empty($email) && !empty($password)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "<div class='alert alert-danger mb-3'>Invalid email format.</div>";
            } else {
            $sql = "SELECT users.id, users.username, users.password_hash, users.profile_picture, roles.role_name 
                    FROM users 
                    JOIN roles ON users.role_id = roles.id 
                    WHERE email = ?";
            if ($stmt = mysqli_prepare($conn_user, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $profile_picture, $role_name);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["profile_picture"] = $profile_picture;
                            $_SESSION["role_name"] = $role_name;
                            
                            if ($role_name === 'Admin') {
                                header("location: admin.php");
                            } else {
                                header("location: index.php");
                            }
                            exit;
                        } else {
                            $message = "<div class='alert alert-danger mb-3'>Invalid email or password.</div>";
                        }
                    }
                } else {
                    $message = "<div class='alert alert-danger mb-3'>Invalid email or password.</div>";
                }
                mysqli_stmt_close($stmt);
            }
            }
        } else {
            $message = "<div class='alert alert-danger mb-3'>Please enter email and password.</div>";
        }
    }

    include('header.php'); 
?>

<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        margin: 0;
    }

    input::-ms-reveal,
    input::-ms-clear {
        display: none;
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .btn-primary {
        background-color: #4e73df;
        border: none;
        padding: 12px;
        font-weight: 600;
    }
    
    /* Ensure header sits above the vh-100 container nicely */
    .header-container {
        position: relative;
        z-index: 100;
    }
</style>

    <div class="container d-flex align-items-center justify-content-center vh-100">
        <div class="row w-100 justify-content-center">
            
            <div class="col-11 col-md-6 col-lg-4">
                
                <div class="text-center d-none d-md-block mb-3">
                    <span class="badge bg-primary text-white">Secure Access Mode</span>
                </div>

                <div class="card p-4 shadow">
                    <h2 class="text-center fw-bold mb-3">Login</h2>
                    <p class="text-center text-muted mb-4 small">Welcome back! Please enter your details.</p>
                    
                    <form id="loginForm" method="POST" action="login.php" novalidate>
                        <?php echo $message; ?>
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-bold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-bold">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    Show
                                </button>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill">Login</button>
                        </div>

                        <div class="text-center mt-3">
                            <span class="small text-muted">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Sign Up</a></span>
                            <br>
                            <a href="#" class="small text-muted mt-2 d-inline-block" data-bs-toggle="modal" data-bs-target="#securityModal">Security Info</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="securityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Security Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-muted">
                    Your data is processed locally for this internship task. Please ensure you use a strong password for Task 2.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Show' : 'Hide';
        });
        // Validation Logic
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const email = document.getElementById('email');
            const password = document.getElementById('password');

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            } else {
                email.classList.remove('is-invalid');
            }

            // Password validation (just checking if not empty for login)
            if(password.value.trim() === '') {
                password.classList.add('is-invalid');
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
            }

            if(isValid) {
                e.target.submit();
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include('footer.php'); ?>