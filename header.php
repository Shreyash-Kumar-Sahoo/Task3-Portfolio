<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $my_name = "Shreyash Kumar Sahoo";
    $my_university = "C. V. Raman Global University";

    $my_projects = [
        ["title" => "Aurawings", "tech" => "PHP & MySQL", "status" => "Completed"],
        ["title" => "Accident Detection", "tech" => "Python & ML", "status" => "Completed"],
        ["title" => "AI Resume Parser", "tech" => "Python & NLP", "status" => "Completed"],
        ["title" => "Autocorrect AI", "tech" => "Python", "status" => "Completed"],
        ["title" => "New AI Project", "tech" => "Python & LLM", "status" => "In Progress"]
    ];

    $academic_history = [
        ["level" => "B.Tech (AI & Data Science)", "inst" => "C. V. Raman Global University", "status" => "4th Year", "year" => "2027"],
        ["level" => "12th Standard", "inst" => "Higher Secondary School", "status" => "Passed", "year" => "2023"],
        ["level" => "10th Standard", "inst" => "Secondary School", "status" => "Passed", "year" => "2021"]
    ];

    function getStatusBadge($status) {
        $color = ($status == "Completed") ? "#d2b48c" : "#a88e7a";
        return "<span style='color:$color; font-weight:bold;'>$status</span>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Shreyash Returns!'; ?></title>
    
    <?php if(isset($use_bootstrap) && $use_bootstrap): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <?php endif; ?>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-area">
                <h1>Shreyash Kumar Sahoo | Student</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php#home"><span class="material-symbols-outlined nav-icon">home</span> HOME</a></li>
                    <li><a href="index.php#projects-section"><span class="material-symbols-outlined nav-icon">work</span> PROJECTS</a></li>
                    <li><a href="index.php#contact"><span class="material-symbols-outlined nav-icon">mail</span> CONNECT</a></li>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <?php if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Admin'): ?>
                            <li><a href="admin.php"><span class="material-symbols-outlined nav-icon">admin_panel_settings</span> ADMIN</a></li>
                        <?php endif; ?>
                        
                        <?php
                        $nav_pic = "uploads/default.png";
                        if (!empty($_SESSION['profile_picture'])) {
                            if (filter_var($_SESSION['profile_picture'], FILTER_VALIDATE_URL)) {
                                $nav_pic = $_SESSION['profile_picture'];
                            } else {
                                $nav_pic = "uploads/" . $_SESSION['profile_picture'];
                            }
                        }
                        $nav_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Profile";
                        ?>
                        <li>
                            <a href="profile.php" style="display:flex; align-items:center; gap:8px;">
                                <img src="<?php echo htmlspecialchars($nav_pic); ?>" alt="Avatar" style="width:30px; height:30px; border-radius:50%; object-fit:cover; border: 2px solid white;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($nav_username); ?>&background=random';">
                                <?php echo $nav_username; ?>
                            </a>
                        </li>

                        <li><a href="logout.php"><span class="material-symbols-outlined nav-icon">logout</span> LOGOUT</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><span class="material-symbols-outlined nav-icon">login</span> LOGIN</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>