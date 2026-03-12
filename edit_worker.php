<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    echo "Worker ID missing!";
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$errors = [];

// Fetch worker data
$query = "SELECT * FROM workers WHERE id='$id'";
$result = mysqli_query($conn, $query);
$worker = mysqli_fetch_assoc($result);

if(!$worker){
    echo "Worker not found!";
    exit();
}

// Get project details
$project_query = "SELECT project_name FROM projects WHERE id='".$worker['project_id']."'";
$project_result = mysqli_query($conn, $project_query);
$project = mysqli_fetch_assoc($project_result);


// UPDATE WORKER
if(isset($_POST['update_worker'])){

    // Sanitize inputs
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $daily_wage = mysqli_real_escape_string($conn, trim($_POST['daily_wage']));
    $joining_date = mysqli_real_escape_string($conn, trim($_POST['joining_date']));

    // Validation
    if(empty($name)){
        $errors['name'] = "Worker name is required";
    }
    elseif(strlen($name) < 3){
        $errors['name'] = "Name must be at least 3 characters";
    }

    if(empty($phone)){
        $errors['phone'] = "Phone number is required";
    }
    elseif(!preg_match("/^[0-9]{10}$/", $phone)){
        $errors['phone'] = "Enter a valid 10-digit phone number";
    }

    if(empty($daily_wage)){
        $errors['daily_wage'] = "Daily wage is required";
    }
    elseif($daily_wage < 100){
        $errors['daily_wage'] = "Daily wage must be at least ₹100";
    }
    elseif($daily_wage > 10000){
        $errors['daily_wage'] = "Daily wage cannot exceed ₹10,000";
    }

    if(empty($joining_date)){
        $errors['joining_date'] = "Joining date is required";
    }

    // Update database if no errors
    if(empty($errors)){

        $update = "UPDATE workers 
                   SET name='$name',
                       phone='$phone',
                       daily_wage='$daily_wage',
                       joining_date='$joining_date'
                   WHERE id='$id'";

        if(mysqli_query($conn, $update)){

            $_SESSION['success_message'] = "Worker updated successfully!";
            header("Location: project_workers.php?project_id=".$worker['project_id']);
            exit();

        }else{
            $error = "Update Failed: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Worker | MAA TARA BUILDERS</title>
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
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: float 20s infinite;
        }

        .circle-1 {
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }

        .circle-2 {
            width: 300px;
            height: 300px;
            bottom: -50px;
            left: -50px;
            animation-delay: 5s;
        }

        .circle-3 {
            width: 200px;
            height: 200px;
            bottom: 50%;
            right: 20%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* Main container */
        .edit-container {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
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

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
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
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 30px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .worker-avatar-large {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 600;
            color: white;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .header-text h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-text h1 i {
            font-size: 24px;
            opacity: 0.9;
        }

        .project-context {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            backdrop-filter: blur(5px);
            width: fit-content;
        }

        .project-context i {
            font-size: 12px;
        }

        /* Form body */
        .form-body {
            padding: 30px;
            background: white;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #1e293b;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .label-icon {
            margin-right: 6px;
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
            z-index: 1;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: #1e293b;
        }

        .form-control.with-prefix {
            padding-left: 75px;
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
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .error-message i {
            font-size: 12px;
        }

        /* Phone input specific */
        .phone-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .country-code {
            position: absolute;
            left: 45px;
            color: #1e293b;
            font-weight: 500;
            font-size: 15px;
            z-index: 1;
            background: #f1f5f9;
            padding: 0 5px;
            border-radius: 4px;
        }

        /* Button */
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
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

        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
        }

        .btn i {
            font-size: 16px;
        }

        .btn-primary.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-primary.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Wage preview cards */
        .wage-preview {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea08 0%, #764ba208 100%);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
        }

        .preview-title {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .preview-title i {
            color: #667eea;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .preview-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .preview-label {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .preview-value {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .preview-value i {
            color: #667eea;
            margin-right: 2px;
            font-size: 12px;
        }

        /* Alert messages */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-error i {
            color: #ef4444;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        /* Character counter */
        .char-counter {
            position: absolute;
            right: 12px;
            bottom: 12px;
            font-size: 11px;
            color: #94a3b8;
            background: white;
            padding: 2px 6px;
            border-radius: 10px;
            pointer-events: none;
            border: 1px solid #e2e8f0;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .edit-container {
                max-width: 100%;
            }

            .header {
                padding: 20px;
            }

            .header-title {
                flex-direction: column;
                text-align: center;
            }

            .form-body {
                padding: 20px;
            }

            .preview-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Animated background -->
    <div class="bg-pattern">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
    </div>

    <div class="edit-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <a href="project_workers.php?project_id=<?php echo $worker['project_id']; ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Workers
                </a>
                
                <div class="header-title">
                    <div class="worker-avatar-large">
                        <?php echo strtoupper(substr($worker['name'], 0, 1)); ?>
                    </div>
                    <div class="header-text">
                        <h1>
                            <i class="fas fa-user-edit"></i>
                            Edit Worker
                        </h1>
                        <div class="project-context">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($project['project_name'] ?? 'Project'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="form-body">
            <?php if(isset($error)) { ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php } ?>

            <form method="POST" id="editWorkerForm" onsubmit="return validateForm()">
                <!-- Worker Name -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-user label-icon"></i>
                        Worker Name
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input 
                            type="text" 
                            name="name" 
                            class="form-control <?php echo isset($errors['name']) ? 'error' : ''; ?>" 
                            placeholder="Enter full name"
                            value="<?php echo htmlspecialchars($worker['name']); ?>"
                            required
                            maxlength="50"
                            id="workerName"
                        >
                    </div>
                    <?php if(isset($errors['name'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['name']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Phone Number -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-phone label-icon"></i>
                        Phone Number
                    </label>
                    <div class="phone-wrapper">
                        <i class="fas fa-phone input-icon"></i>
                        <span class="country-code">+91</span>
                        <input 
                            type="tel" 
                            name="phone" 
                            class="form-control with-prefix <?php echo isset($errors['phone']) ? 'error' : ''; ?>" 
                            placeholder="9876543210"
                            value="<?php echo htmlspecialchars($worker['phone']); ?>"
                            required
                            maxlength="10"
                            pattern="[0-9]{10}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        >
                    </div>
                    <?php if(isset($errors['phone'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['phone']; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Joining Date -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar label-icon"></i>
                        Joining Date
                    </label>

                    <div class="input-wrapper">
                        <i class="fas fa-calendar input-icon"></i>

                        <input 
                            type="date" 
                            name="joining_date" 
                            class="form-control <?php echo isset($errors['joining_date']) ? 'error' : ''; ?>"
                            value="<?php echo htmlspecialchars($worker['joining_date']); ?>"
                            required
                        >
                    </div>

                    <?php if(isset($errors['joining_date'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['joining_date']; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Daily Wage -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-rupee-sign label-icon"></i>
                        Daily Wage (₹)
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-rupee-sign input-icon"></i>
                        <input 
                            type="number" 
                            name="daily_wage" 
                            class="form-control <?php echo isset($errors['daily_wage']) ? 'error' : ''; ?>" 
                            placeholder="500"
                            value="<?php echo htmlspecialchars($worker['daily_wage']); ?>"
                            required
                            min="100"
                            max="10000"
                            step="10"
                            id="dailyWage"
                            oninput="updateWagePreview(this.value)"
                        >
                    </div>
                    <?php if(isset($errors['daily_wage'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['daily_wage']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Wage Preview -->
                <div class="wage-preview" id="wagePreview">
                    <div class="preview-title">
                        <i class="fas fa-chart-line"></i>
                        Wage Calculations
                    </div>
                    <div class="preview-grid">
                        <div class="preview-item">
                            <div class="preview-label">Daily</div>
                            <div class="preview-value" id="dailyWageDisplay">
                                <i class="fas fa-rupee-sign"></i><?php echo number_format($worker['daily_wage']); ?>
                            </div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Monthly (26 days)</div>
                            <div class="preview-value" id="monthlyWageDisplay">
                                <i class="fas fa-rupee-sign"></i><?php echo number_format($worker['daily_wage'] * 26); ?>
                            </div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-label">Yearly</div>
                            <div class="preview-value" id="yearlyWageDisplay">
                                <i class="fas fa-rupee-sign"></i><?php echo number_format($worker['daily_wage'] * 312); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-group">
                    <a href="project_workers.php?project_id=<?php echo $worker['project_id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" name="update_worker" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i>
                        Update Worker
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form validation
        function validateForm() {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Updating...';
            return true;
        }

        // Update wage preview
        function updateWagePreview(wage) {
            const dailyDisplay = document.getElementById('dailyWageDisplay');
            const monthlyDisplay = document.getElementById('monthlyWageDisplay');
            const yearlyDisplay = document.getElementById('yearlyWageDisplay');

            if(wage && wage > 0) {
                const numWage = Number(wage);
                dailyDisplay.innerHTML = '<i class="fas fa-rupee-sign"></i>' + numWage.toLocaleString('en-IN');
                monthlyDisplay.innerHTML = '<i class="fas fa-rupee-sign"></i>' + (numWage * 26).toLocaleString('en-IN');
                yearlyDisplay.innerHTML = '<i class="fas fa-rupee-sign"></i>' + (numWage * 312).toLocaleString('en-IN');
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

        // Character counter for name
        const nameInput = document.getElementById('workerName');
        if(nameInput) {
            const counter = document.createElement('span');
            counter.className = 'char-counter';
            nameInput.parentElement.appendChild(counter);
            
            function updateCounter() {
                const length = nameInput.value.length;
                counter.textContent = `${length}/50`;
                counter.style.color = length > 45 ? '#ef4444' : '#64748b';
            }
            
            nameInput.addEventListener('input', updateCounter);
            updateCounter();
        }

        // Phone number formatting
        const phoneInput = document.querySelector('input[name="phone"]');
        if(phoneInput) {
            phoneInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if(value.length > 10) value = value.slice(0, 10);
                this.value = value;
            });
        }

        // Add animation to input focus
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                const icon = this.parentElement.querySelector('.input-icon');
                if(icon) icon.style.color = '#667eea';
            });
            
            input.addEventListener('blur', function() {
                const icon = this.parentElement.querySelector('.input-icon');
                if(icon) icon.style.color = '#94a3b8';
            });
        });

        // Prevent negative values in wage
        const wageInput = document.getElementById('dailyWage');
        if(wageInput) {
            wageInput.addEventListener('input', function() {
                if(this.value < 0) this.value = 100;
                if(this.value > 10000) this.value = 10000;
            });
        }

        // Auto-dismiss alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>