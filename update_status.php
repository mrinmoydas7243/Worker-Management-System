<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin']))
{
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id']))
{
    echo "Project ID Missing";
    exit();
}

$id = $_GET['id'];

$result = mysqli_query($conn,"SELECT * FROM projects WHERE id='$id'");
$project = mysqli_fetch_assoc($result);

if(isset($_POST['update_status']))
{
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    mysqli_query($conn,"UPDATE projects SET status='$status' WHERE id='$id'");

    $_SESSION['success_message'] = "Project status updated successfully!";
    header("Location: ../dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Project Status | MAA TARA BUILDERS</title>
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
        .status-container {
            width: 100%;
            max-width: 500px;
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
        }

        .status-icon-large {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .header-text h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .header-text p {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Form body */
        .form-body {
            padding: 30px;
            background: white;
        }

        /* Project info card */
        .project-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        .project-name {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .project-name i {
            color: #667eea;
        }

        .project-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .detail-item {
            flex: 1;
            min-width: 120px;
        }

        .detail-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
        }

        .current-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-pending {
            background: #feebc8;
            color: #744210;
        }

        .status-completed {
            background: #bee3f8;
            color: #1e4a6b;
        }

        /* Form group */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #1e293b;
            font-weight: 500;
            font-size: 14px;
        }

        .label-icon {
            margin-right: 6px;
            color: #667eea;
        }

        /* Custom select styling */
        .select-wrapper {
            position: relative;
            margin-bottom: 10px;
        }

        .select-wrapper::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            pointer-events: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 500;
            appearance: none;
            background: #f8fafc;
            color: #1e293b;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        select:hover {
            background: white;
            border-color: #cbd5e1;
        }

        select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        /* Status preview cards */
        .status-preview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .status-option {
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0.7;
        }

        .status-option:hover {
            transform: translateY(-2px);
            border-color: #94a3b8;
            opacity: 1;
        }

        .status-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
            opacity: 1;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.1);
        }

        .status-option .icon {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .status-option .label {
            font-size: 12px;
            font-weight: 500;
        }

        .status-option.active .icon { color: #10b981; }
        .status-option.pending .icon { color: #f59e0b; }
        .status-option.completed .icon { color: #3b82f6; }

        /* Button */
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 16px;
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

        /* Responsive */
        @media (max-width: 640px) {
            .status-container {
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

            .status-preview {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .project-details {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Status badge animation */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .current-status {
            animation: pulse 2s infinite;
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
    <!-- Animated background -->
    <div class="bg-pattern">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
    </div>

    <div class="status-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <a href="../dashboard.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
                
                <div class="header-title">
                    <div class="status-icon-large">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="header-text">
                        <h1>Update Status</h1>
                        <p>Change project progress status</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="form-body">
            <!-- Project Info Card -->
            <div class="project-card">
                <div class="project-name">
                    <i class="fas fa-building"></i>
                    <?php echo htmlspecialchars($project['project_name']); ?>
                </div>
                
                <div class="project-details">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Location
                        </div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($project['location'] ?? 'Not specified'); ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-calendar-alt"></i>
                            Start Date
                        </div>
                        <div class="detail-value">
                            <?php echo date('d M Y', strtotime($project['start_date'])); ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-tag"></i>
                            Current Status
                        </div>
                        <div class="detail-value">
                            <span class="current-status status-<?php echo strtolower($project['status']); ?>">
                                <i class="fas fa-<?php 
                                    echo $project['status'] == 'active' ? 'play-circle' : 
                                        ($project['status'] == 'completed' ? 'check-circle' : 'clock'); 
                                ?>"></i>
                                <?php echo ucfirst($project['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" id="statusForm" onsubmit="return validateForm()">
                <!-- Status Selection -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-tasks label-icon"></i>
                        Select New Status
                        <span class="tooltip" data-tooltip="Choose the current progress of the project">?</span>
                    </label>
                    
                    <div class="select-wrapper">
                        <select name="status" id="statusSelect" required onchange="updateSelectedStatus(this.value)">
                            <option value="" disabled>-- Select Status --</option>
                            <option value="active" <?php if($project['status']=="active") echo "selected"; ?>>Active</option>
                            <option value="pending" <?php if($project['status']=="pending") echo "selected"; ?>>Pending</option>
                            <option value="completed" <?php if($project['status']=="completed") echo "selected"; ?>>Completed</option>
                        </select>
                    </div>

                    <!-- Visual Status Preview Cards -->
                    <div class="status-preview">
                        <div class="status-option active <?php echo $project['status'] == 'active' ? 'selected' : ''; ?>" 
                             onclick="selectStatus('active')"
                             data-status="active">
                            <div class="icon"><i class="fas fa-play-circle"></i></div>
                            <div class="label">Active</div>
                        </div>
                        <div class="status-option pending <?php echo $project['status'] == 'pending' ? 'selected' : ''; ?>" 
                             onclick="selectStatus('pending')"
                             data-status="pending">
                            <div class="icon"><i class="fas fa-clock"></i></div>
                            <div class="label">Pending</div>
                        </div>
                        <div class="status-option completed <?php echo $project['status'] == 'completed' ? 'selected' : ''; ?>" 
                             onclick="selectStatus('completed')"
                             data-status="completed">
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                            <div class="label">Completed</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-group">
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" name="update_status" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i>
                        Update Status
                    </button>
                </div>
            </form>

            <!-- Status Change Info -->
            <div style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 12px; border-left: 4px solid #667eea;">
                <div style="display: flex; gap: 10px; align-items: flex-start;">
                    <i class="fas fa-info-circle" style="color: #667eea; margin-top: 2px;"></i>
                    <div>
                        <p style="color: #1e293b; font-size: 13px; margin-bottom: 5px; font-weight: 500;">
                            Status Change Impact:
                        </p>
                        <p style="color: #64748b; font-size: 12px; line-height: 1.5;">
                            <i class="fas fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 5px; color: #10b981;"></i>
                            <strong>Active:</strong> Project is ongoing and workers can be assigned<br>
                            <i class="fas fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 5px; color: #f59e0b;"></i>
                            <strong>Pending:</strong> Project is on hold, no new assignments<br>
                            <i class="fas fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 5px; color: #3b82f6;"></i>
                            <strong>Completed:</strong> Project finished, archive mode
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation with loading state
        function validateForm() {
            const status = document.getElementById('statusSelect').value;
            if(!status) {
                alert('Please select a status');
                return false;
            }

            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Updating Status...';
            return true;
        }

        // Select status from preview cards
        function selectStatus(status) {
            // Update select dropdown
            const select = document.getElementById('statusSelect');
            select.value = status;
            
            // Update visual selection
            document.querySelectorAll('.status-option').forEach(option => {
                if(option.dataset.status === status) {
                    option.classList.add('selected');
                } else {
                    option.classList.remove('selected');
                }
            });
        }

        // Update selected status when dropdown changes
        function updateSelectedStatus(status) {
            document.querySelectorAll('.status-option').forEach(option => {
                if(option.dataset.status === status) {
                    option.classList.add('selected');
                } else {
                    option.classList.remove('selected');
                }
            });
        }

        // Add hover effects
        document.querySelectorAll('.status-option').forEach(option => {
            option.addEventListener('mouseenter', function() {
                if(!this.classList.contains('selected')) {
                    this.style.transform = 'translateY(-2px)';
                }
            });
            
            option.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Add animation to current status badge
        const currentStatus = document.querySelector('.current-status');
        if(currentStatus) {
            setInterval(() => {
                currentStatus.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    currentStatus.style.transform = 'scale(1)';
                }, 200);
            }, 3000);
        }

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Add keyboard shortcut (Ctrl+Enter to submit)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('statusForm').submit();
            }
        });

        // Show confirmation if changing from completed status
        document.getElementById('statusForm').addEventListener('submit', function(e) {
            const currentStatus = '<?php echo $project['status']; ?>';
            const newStatus = document.getElementById('statusSelect').value;
            
            if(currentStatus === 'completed' && newStatus !== 'completed') {
                if(!confirm('This project is marked as completed. Are you sure you want to change it back to ' + newStatus + '?')) {
                    e.preventDefault();
                    const btn = document.getElementById('submitBtn');
                    btn.classList.remove('loading');
                    btn.innerHTML = '<i class="fas fa-save"></i> Update Status';
                }
            }
        });
    </script>
</body>
</html>