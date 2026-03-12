<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin']))
{
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['project_id']))
{
    header("Location: ../dashboard.php");
}

$project_id = $_GET['project_id'];

// Get project details
$project_query = "SELECT project_name FROM projects WHERE id='$project_id'";
$project_result = mysqli_query($conn, $project_query);
$project = mysqli_fetch_assoc($project_result);

$name = $phone = $daily_wage = $joining_date = "";
$errors = [];

if(isset($_POST['add_worker']))
{
    // Sanitize inputs
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $daily_wage = mysqli_real_escape_string($conn, trim($_POST['daily_wage']));
    $joining_date = mysqli_real_escape_string($conn, $_POST['joining_date']);

    // Validation
    if(empty($name)) {
        $errors['name'] = "Worker name is required";
    } elseif(strlen($name) < 3) {
        $errors['name'] = "Name must be at least 3 characters";
    }

    if(empty($phone)) {
        $errors['phone'] = "Phone number is required";
    } elseif(!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = "Enter a valid 10-digit phone number";
    }

    if(empty($daily_wage)) {
        $errors['daily_wage'] = "Daily wage is required";
    } elseif($daily_wage < 100) {
        $errors['daily_wage'] = "Daily wage must be at least ₹100";
    } elseif($daily_wage > 10000) {
        $errors['daily_wage'] = "Daily wage cannot exceed ₹10,000";
    }

    if(empty($joining_date)) {
        $errors['joining_date'] = "Joining date is required";
    }

    // Insert worker
    if(empty($errors)) {

        $query = "INSERT INTO workers (project_id, name, phone, daily_wage, joining_date)
                  VALUES ('$project_id', '$name', '$phone', '$daily_wage', '$joining_date')";

        if(mysqli_query($conn, $query)) {

            $_SESSION['success_message'] = "Worker added successfully!";
            header("Location: project_workers.php?project_id=".$project_id);
            exit();

        } else {
            $error = "Error adding worker: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Worker | MAA TARA BUILDERS</title>
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
        .worker-container {
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
            position: relative;
            z-index: 1;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header-content h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-content h1 i {
            font-size: 24px;
            opacity: 0.9;
        }

        .project-context {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 30px;
            margin-top: 10px;
            font-size: 14px;
            backdrop-filter: blur(5px);
            width: fit-content;
        }

        .project-context i {
            font-size: 14px;
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

        /* Input hint */
        .input-hint {
            margin-top: 4px;
            font-size: 12px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .input-hint i {
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
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
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
            margin-top: 10px;
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

        /* Wage preview - FIXED STYLING */
        .wage-preview {
            margin-top: 15px;
            padding: 15px;
            background: linear-gradient(135deg, #667eea08 0%, #764ba208 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .wage-preview-item {
            text-align: center;
            flex: 1;
            padding: 5px;
        }

        .wage-preview-label {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .wage-preview-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }

        .wage-preview-value i {
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
            body {
                padding: 10px;
            }

            .header {
                padding: 20px;
            }

            .header-content h1 {
                font-size: 24px;
            }

            .form-body {
                padding: 20px;
            }

            .wage-preview {
                flex-direction: column;
                gap: 10px;
            }

            .wage-preview-item {
                width: 100%;
            }
        }

        /* Form footer */
        .form-footer {
            margin-top: 25px;
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

    <div class="worker-container">
        <!-- Header -->
        <div class="header">
            <a href="../projects/project_page.php?id=<?php echo $project_id; ?>" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Project
            </a>
            
            <div class="header-content">
                <h1>
                    <i class="fas fa-user-plus"></i>
                    Add New Worker
                </h1>
                
                <?php if(isset($project['project_name'])): ?>
                <div class="project-context">
                    <i class="fas fa-building"></i>
                    Adding to: <strong><?php echo htmlspecialchars($project['project_name']); ?></strong>
                </div>
                <?php endif; ?>
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

            <form method="POST" id="workerForm" onsubmit="return validateForm()">
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
                            value="<?php echo htmlspecialchars($name); ?>"
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
                    <div class="input-hint">
                        <i class="fas fa-info-circle"></i>
                        Enter worker's full name as per records
                    </div>
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
                            value="<?php echo htmlspecialchars($phone); ?>"
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
                    <div class="input-hint">
                        <i class="fas fa-info-circle"></i>
                        Enter 10-digit mobile number without country code
                    </div>
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
                            value="<?php echo htmlspecialchars($daily_wage); ?>"
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

                    <!-- Wage Preview -->
                    <div class="wage-preview" id="wagePreview" style="<?php echo empty($daily_wage) ? 'display: none;' : 'display: flex;'; ?>">
                        <div class="wage-preview-item">
                            <div class="wage-preview-label">Daily</div>
                            <div class="wage-preview-value" id="dailyWageDisplay">
                                <i class="fas fa-rupee-sign"></i><?php echo number_format($daily_wage ?: 0); ?>
                            </div>
                        </div>
                        <div class="wage-preview-item">
                            <div class="wage-preview-label">Monthly</div>
                            <div class="wage-preview-value" id="monthlyWageDisplay">
                                <i class="fas fa-rupee-sign"></i><?php echo number_format(($daily_wage ?: 0) * 26); ?>
                            </div>
                        </div>
                        <div class="wage-preview-item">
                            <div class="wage-preview-label">Yearly</div>
                            <div class="wage-preview-value" id="yearlyWageDisplay">
                                <i class="fas fa-rupee-sign"></i><?php echo number_format(($daily_wage ?: 0) * 312); ?>
                            </div>
                        </div>
                    </div>
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
                            value="<?php echo htmlspecialchars($joining_date); ?>"
                            required
                        >
                    </div>

                    <?php if(isset($errors['joining_date'])): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['joining_date']; ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-hint">
                        <i class="fas fa-info-circle"></i>
                        Select the worker joining date
                    </div>
                </div>
                <!-- Form Footer with Submit Button -->
                <div class="form-footer">
                    <button type="submit" name="add_worker" class="btn" id="submitBtn">
                        <span>Add Worker</span>
                        <i class="fas fa-user-plus"></i>
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
            btn.innerHTML = '<i class="fas fa-spinner"></i> Adding Worker...';
            return true;
        }

        // Update wage preview
        function updateWagePreview(wage) {
            const preview = document.getElementById('wagePreview');
            const dailyDisplay = document.getElementById('dailyWageDisplay');
            const monthlyDisplay = document.getElementById('monthlyWageDisplay');
            const yearlyDisplay = document.getElementById('yearlyWageDisplay');

            if(wage && wage > 0) {
                preview.style.display = 'flex';
                const numWage = Number(wage);
                dailyDisplay.innerHTML = '<i class="fas fa-rupee-sign"></i>' + numWage.toLocaleString('en-IN');
                monthlyDisplay.innerHTML = '<i class="fas fa-rupee-sign"></i>' + (numWage * 26).toLocaleString('en-IN');
                yearlyDisplay.innerHTML = '<i class="fas fa-rupee-sign"></i>' + (numWage * 312).toLocaleString('en-IN');
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

        // Auto-dismiss alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

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
    </script>
</body>
</html>