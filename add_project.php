<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin']))
{
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$project_name = $location = $start_date = $status = "";
$errors = [];

if(isset($_POST['add_project']))
{
    // Sanitize and validate inputs
    $project_name = mysqli_real_escape_string($conn, trim($_POST['project_name']));
    $location = mysqli_real_escape_string($conn, trim($_POST['location']));
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Validation
    if(empty($project_name)) {
        $errors['project_name'] = "Project name is required";
    } elseif(strlen($project_name) < 3) {
        $errors['project_name'] = "Project name must be at least 3 characters";
    }

    if(empty($location)) {
        $errors['location'] = "Location is required";
    }

    if(empty($start_date)) {
        $errors['start_date'] = "Start date is required";
    }

    if(empty($status)) {
        $errors['status'] = "Please select a status";
    }

    // If no errors, insert into database
    if(empty($errors)) {
        $query = "INSERT INTO projects (project_name, location, start_date, status)
                  VALUES ('$project_name', '$location', '$start_date', '$status')";

        if(mysqli_query($conn, $query))
        {
            $_SESSION['success_message'] = "Project added successfully!";
            header("Location: ../dashboard.php");
            exit();
        }
        else
        {
            $error = "Error adding project: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Project | MAA TARA BUILDERS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        /* Animated background elements */
        .bg-shape {
            position: fixed;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
        }

        .shape-1 {
            top: -100px;
            left: -100px;
            animation: float 6s ease-in-out infinite;
        }

        .shape-2 {
            bottom: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .container {
            background: white;
            width: 100%;
            max-width: 550px;
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header with gradient */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 40px 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .header h2 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header h2 i {
            font-size: 28px;
            opacity: 0.9;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            line-height: 1.5;
        }

        .header .project-count {
            position: absolute;
            bottom: 20px;
            right: 30px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
            backdrop-filter: blur(5px);
        }

        /* Form body */
        .form-body {
            padding: 40px;
            background: white;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1e293b;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .label-icon {
            margin-right: 8px;
            color: #667eea;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: #94a3b8;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: #1e293b;
        }

        .form-control:hover {
            background: white;
            border-color: #cbd5e1;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        .form-control.error {
            border-color: #f56565;
            background: #fff5f5;
        }

        .error-message {
            color: #f56565;
            font-size: 13px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .error-message i {
            font-size: 12px;
        }

        /* Custom select styling */
        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            font-size: 14px;
        }

        select.form-control {
            appearance: none;
            cursor: pointer;
            padding-right: 45px;
        }

        select.form-control option {
            padding: 12px;
            background: white;
            color: #1e293b;
        }

        /* Status preview */
        .status-preview {
            margin-top: 10px;
            padding: 10px 15px;
            background: #f8fafc;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #64748b;
            border: 1px dashed #cbd5e1;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.ongoing { background: #f6ad55; }
        .status-dot.completed { background: #48bb78; }
        .status-dot.pending { background: #4299e1; }

        /* Button styling */
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .btn:hover i {
            transform: translateX(5px);
        }

        /* Back link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
            padding: 10px 20px;
            border-radius: 12px;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateX(-5px);
        }

        /* Alert messages */
        .alert {
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }

        .alert i {
            font-size: 20px;
        }

        /* Date input styling */
        input[type="date"].form-control {
            padding-right: 15px;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
            padding: 5px;
        }

        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .header {
                padding: 30px 20px;
            }

            .header h2 {
                font-size: 26px;
            }

            .form-body {
                padding: 30px 20px;
            }

            .form-control {
                padding: 12px 12px 12px 40px;
                font-size: 14px;
            }
        }

        /* Loading state */
        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
            margin-left: 5px;
            color: #94a3b8;
            cursor: help;
        }

        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
        }
    </style>
</head>
<body>
    <!-- Animated background shapes -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <div class="container">
        <div class="header">
            <h2>
                <i class="fas fa-plus-circle"></i>
                Add New Project
            </h2>
            <p>Fill in the project details below. All fields are required to ensure accurate project tracking.</p>
        </div>

        <div class="form-body">
            <?php if(isset($error)) { ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php } ?>

            <form method="POST" id="projectForm" onsubmit="return validateForm()">
                <!-- Project Name -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-building label-icon"></i>
                        Project Name
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-project-diagram input-icon"></i>
                        <input 
                            type="text" 
                            name="project_name" 
                            class="form-control <?php echo isset($errors['project_name']) ? 'error' : ''; ?>" 
                            placeholder="e.g., Green Valley Residency"
                            value="<?php echo htmlspecialchars($project_name); ?>"
                            required
                            maxlength="100"
                        >
                    </div>
                    <?php if(isset($errors['project_name'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['project_name']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-map-marker-alt label-icon"></i>
                        Location
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-location-dot input-icon"></i>
                        <input 
                            type="text" 
                            name="location" 
                            class="form-control <?php echo isset($errors['location']) ? 'error' : ''; ?>" 
                            placeholder="e.g., Sector 62, Noida"
                            value="<?php echo htmlspecialchars($location); ?>"
                            required
                        >
                    </div>
                    <?php if(isset($errors['location'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['location']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Start Date -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar-alt label-icon"></i>
                        Start Date
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-calendar-day input-icon"></i>
                        <input 
                            type="date" 
                            name="start_date" 
                            class="form-control <?php echo isset($errors['start_date']) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars($start_date); ?>"
                            required
                            max="<?php echo date('Y-m-d'); ?>"
                        >
                    </div>
                    <?php if(isset($errors['start_date'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['start_date']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-tasks label-icon"></i>
                        Status
                    </label>
                    <div class="select-wrapper">
                        <i class="fas fa-flag input-icon"></i>
                        <select name="status" class="form-control <?php echo isset($errors['status']) ? 'error' : ''; ?>" required onchange="updateStatusPreview(this.value)">
                            <option value="" disabled <?php echo empty($status) ? 'selected' : ''; ?>>Select project status</option>
                            <option value="Ongoing" <?php echo $status == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>
                    
                    <!-- Status Preview -->
                    <div class="status-preview" id="statusPreview" style="<?php echo empty($status) ? 'display: none;' : ''; ?>">
                        <span class="status-dot <?php echo strtolower($status); ?>" id="statusDot"></span>
                        <span id="statusText"><?php echo $status; ?> project</span>
                    </div>

                    <?php if(isset($errors['status'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['status']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="add_project" class="btn" id="submitBtn">
                    <span>Add Project</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <!-- Back to Dashboard -->
                <a href="../dashboard.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </form>
        </div>
    </div>

    <script>
        // Form validation
        function validateForm() {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Adding Project...';
            return true;
        }

        // Update status preview
        function updateStatusPreview(status) {
            const preview = document.getElementById('statusPreview');
            const dot = document.getElementById('statusDot');
            const text = document.getElementById('statusText');

            if(status) {
                preview.style.display = 'flex';
                dot.className = 'status-dot ' + status.toLowerCase();
                text.textContent = status + ' project';
            } else {
                preview.style.display = 'none';
            }
        }

        // Real-time validation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                if(this.classList.contains('error')) {
                    this.classList.remove('error');
                    const errorMsg = this.parentElement.parentElement.querySelector('.error-message');
                    if(errorMsg) errorMsg.remove();
                }
            });
        });

        // Prevent future dates for start date
        const dateInput = document.querySelector('input[type="date"]');
        if(dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('max', today);
        }

        // Character counter for project name
        const projectNameInput = document.querySelector('input[name="project_name"]');
        if(projectNameInput) {
            const counter = document.createElement('small');
            counter.style.cssText = 'display: block; text-align: right; margin-top: 5px; color: #94a3b8; font-size: 12px;';
            projectNameInput.parentElement.parentElement.appendChild(counter);
            
            function updateCounter() {
                const length = projectNameInput.value.length;
                counter.textContent = `${length}/100 characters`;
                if(length > 90) counter.style.color = '#f56565';
                else counter.style.color = '#94a3b8';
            }
            
            projectNameInput.addEventListener('input', updateCounter);
            updateCounter();
        }

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>