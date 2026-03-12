<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin']))
{
    header("Location: ../login.php");
    exit();
}

$project_id = $_GET['project_id'];

// Get project details
$project_query = "SELECT project_name FROM projects WHERE id='$project_id'";
$project_result = mysqli_query($conn, $project_query);
$project = mysqli_fetch_assoc($project_result);

// Get workers count
$count_query = "SELECT COUNT(*) as total FROM workers WHERE project_id='$project_id'";
$count_result = mysqli_query($conn, $count_query);
$total_workers = mysqli_fetch_assoc($count_result)['total'];

// Check if attendance already taken today
$today = date('Y-m-d');
$attendance_check = "SELECT COUNT(*) as count FROM attendance WHERE project_id='$project_id' AND date='$today'";
$attendance_result = mysqli_query($conn, $attendance_check);
$attendance_taken = mysqli_fetch_assoc($attendance_result)['count'] > 0;

$query = "SELECT * FROM workers WHERE project_id='$project_id' ORDER BY name ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance | MAA TARA BUILDERS</title>
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
            padding: 30px;
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

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* Main container */
        .attendance-container {
            max-width: 1200px;
            margin: 0 auto;
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
            padding: 40px;
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
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .title-section h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .title-section h1 i {
            font-size: 28px;
        }

        .project-name {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 20px;
            border-radius: 30px;
            width: fit-content;
            font-size: 14px;
        }

        .date-badge {
            background: white;
            color: #667eea;
            padding: 12px 24px;
            border-radius: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .date-badge i {
            font-size: 18px;
        }

        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px 40px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 20px;
        }

        .stat-info h3 {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Alert message */
        .alert {
            margin: 30px 40px 0;
            padding: 16px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }

        .alert-warning {
            background: #feebc8;
            color: #744210;
            border-left: 4px solid #f6ad55;
        }

        .alert-warning i {
            color: #f6ad55;
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

        /* Quick actions */
        .quick-actions {
            padding: 30px 40px 0;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .quick-btn {
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .quick-btn:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
        }

        .quick-btn i {
            font-size: 14px;
        }

        /* Form section */
        .form-section {
            padding: 30px 40px 40px;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: white;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 14px;
            padding: 20px 16px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #f8fafc;
        }

        .worker-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .worker-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-weight: 600;
        }

        .worker-details {
            display: flex;
            flex-direction: column;
        }

        .worker-name {
            font-weight: 600;
            color: #1e293b;
        }

        .worker-wage {
            font-size: 12px;
            color: #64748b;
        }

        /* Radio and input styling */
        .radio-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .radio-option span {
            font-size: 14px;
            color: #475569;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-present {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-absent {
            background: #fed7d7;
            color: #742a2a;
        }

        .work-type {
            display: flex;
            gap: 15px;
        }

        .work-option {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .overtime-input {
            width: 100px;
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .overtime-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        .overtime-input::placeholder {
            color: #94a3b8;
        }

        /* Summary bar */
        .summary-bar {
            background: #f8fafc;
            padding: 20px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1e293b;
        }

        .summary-item i {
            color: #667eea;
        }

        .summary-count {
            font-weight: 700;
            font-size: 18px;
            margin-left: 5px;
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 18px;
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

        .submit-btn::before {
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

        .submit-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn i {
            font-size: 18px;
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .header {
                padding: 30px 20px;
            }

            .title-section h1 {
                font-size: 24px;
            }

            .stats-grid {
                padding: 20px;
            }

            .quick-actions {
                padding: 20px 20px 0;
            }

            .form-section {
                padding: 20px;
            }

            .summary-bar {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Loading state */
        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            border: 3px solid #667eea;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Animated background -->
    <div class="bg-pattern">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
    </div>

    <div class="attendance-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <a href="../projects/project_page.php?id=<?php echo $project_id; ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Project
                </a>
                
                <div class="header-title">
                    <div class="title-section">
                        <h1>
                            <i class="fas fa-clipboard-list"></i>
                            Take Attendance
                        </h1>
                        <div class="project-name">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($project['project_name']); ?>
                        </div>
                    </div>
                    
                    <div class="date-badge">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Workers</h3>
                    <div class="stat-number"><?php echo $total_workers; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Current Time</h3>
                    <div class="stat-number" id="currentTime">--:--:--</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Attendance Status</h3>
                    <div class="stat-number" id="markedCount">0/<?php echo $total_workers; ?></div>
                </div>
            </div>
        </div>

        <!-- Alert if attendance already taken -->
        <?php if($attendance_taken): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Attendance has already been taken for today. You can still update it if needed.</span>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="quick-btn" onclick="markAllPresent()">
                <i class="fas fa-check-circle"></i>
                Mark All Present
            </button>
            <button class="quick-btn" onclick="markAllFullDay()">
                <i class="fas fa-sun"></i>
                All Full Day
            </button>
            <button class="quick-btn" onclick="resetAll()">
                <i class="fas fa-undo"></i>
                Reset All
            </button>
        </div>

        <!-- Attendance Form -->
        <div class="form-section">
            <form action="save_attendance.php" method="POST" id="attendanceForm" onsubmit="return validateForm()">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <input type="hidden" name="attendance_date" value="<?php echo date('Y-m-d'); ?>">

                <div class="table-container">
                    <table id="attendanceTable">
                        <thead>
                            <tr>
                                <th>Worker</th>
                                <th>Status</th>
                                <th>Work Type</th>
                                <th>Overtime (Hours)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 0;
                            while($row = mysqli_fetch_assoc($result)): 
                                $initial = strtoupper(substr($row['name'], 0, 1));
                            ?>
                            <tr data-worker-id="<?php echo $row['id']; ?>">
                                <td>
                                    <div class="worker-info">
                                        <div class="worker-avatar">
                                            <?php echo $initial; ?>
                                        </div>
                                        <div class="worker-details">
                                            <span class="worker-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                            <span class="worker-wage">₹<?php echo number_format($row['daily_wage']); ?>/day</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="worker_id[]" value="<?php echo $row['id']; ?>">
                                </td>
                                <td>
                                    <div class="radio-group">
                                        <label class="radio-option">
                                            <input type="radio" name="status[<?php echo $row['id']; ?>]" value="Present" class="status-radio" onchange="updateSummary()" <?php echo $counter == 0 ? 'checked' : ''; ?>>
                                            <span class="status-badge status-present">Present</span>
                                        </label>
                                        <label class="radio-option">
                                            <input type="radio" name="status[<?php echo $row['id']; ?>]" value="Absent" class="status-radio" onchange="updateSummary()">
                                            <span class="status-badge status-absent">Absent</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="work-type">
                                        <label class="work-option">
                                            <input type="radio" name="worktype[<?php echo $row['id']; ?>]" value="Full Day" <?php echo $counter == 0 ? 'checked' : ''; ?>>
                                            <span>Full Day</span>
                                        </label>
                                        <label class="work-option">
                                            <input type="radio" name="worktype[<?php echo $row['id']; ?>]" value="Half Day">
                                            <span>Half Day</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="overtime[<?php echo $row['id']; ?>]" 
                                           class="overtime-input" 
                                           value="0" 
                                           min="0" 
                                           max="24" 
                                           step="0.5"
                                           placeholder="Hours"
                                           onchange="updateSummary()">
                                </td>
                            </tr>
                            <?php 
                            $counter++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Bar -->
                <div class="summary-bar">
                    <div class="summary-item">
                        <i class="fas fa-user-check"></i>
                        <span>Present: <span class="summary-count" id="presentCount">0</span></span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-user-times"></i>
                        <span>Absent: <span class="summary-count" id="absentCount">0</span></span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-clock"></i>
                        <span>Total Overtime: <span class="summary-count" id="totalOvertime">0</span> hrs</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit_attendance" class="submit-btn" id="submitBtn">
                    <i class="fas fa-save"></i>
                    Save Attendance
                </button>
            </form>
        </div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: true 
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Update summary counts
        function updateSummary() {
            let present = 0;
            let absent = 0;
            let totalOvertime = 0;

            document.querySelectorAll('tr[data-worker-id]').forEach(row => {
                const status = row.querySelector('input[name^="status"]:checked');
                const overtime = row.querySelector('.overtime-input').value || 0;

                if (status) {
                    if (status.value === 'Present') {
                        present++;
                    } else if (status.value === 'Absent') {
                        absent++;
                    }
                }

                totalOvertime += parseFloat(overtime);
            });

            document.getElementById('presentCount').textContent = present;
            document.getElementById('absentCount').textContent = absent;
            document.getElementById('totalOvertime').textContent = totalOvertime.toFixed(1);
            document.getElementById('markedCount').textContent = present + '/' + document.querySelectorAll('tr[data-worker-id]').length;
        }

        // Mark all present
        function markAllPresent() {
            document.querySelectorAll('input[value="Present"]').forEach(radio => {
                radio.checked = true;
            });
            updateSummary();
        }

        // Mark all full day
        function markAllFullDay() {
            document.querySelectorAll('input[value="Full Day"]').forEach(radio => {
                radio.checked = true;
            });
        }

        // Reset all
        function resetAll() {
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.checked = false;
            });
            document.querySelectorAll('.overtime-input').forEach(input => {
                input.value = '0';
            });
            // Check first worker's present by default
            const firstPresent = document.querySelector('input[value="Present"]');
            if (firstPresent) firstPresent.checked = true;
            const firstFullDay = document.querySelector('input[value="Full Day"]');
            if (firstFullDay) firstFullDay.checked = true;
            updateSummary();
        }

        // Form validation
        function validateForm() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Check if at least one worker has status selected
            let hasStatus = false;
            document.querySelectorAll('input[name^="status"]:checked').forEach(() => {
                hasStatus = true;
            });

            if (!hasStatus) {
                alert('Please mark attendance for at least one worker.');
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                return false;
            }

            return true;
        }

        // Initialize summary on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+S to submit form
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                document.getElementById('attendanceForm').submit();
            }
            // Ctrl+A to mark all present
            if (e.ctrlKey && e.key === 'a') {
                e.preventDefault();
                markAllPresent();
            }
        });

        // Confirm if leaving with unsaved changes
        let formChanged = false;
        document.getElementById('attendanceForm').addEventListener('change', function() {
            formChanged = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>