<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    echo "Project ID missing!";
    exit();
}

$project_id = $_GET['id'];

/* ===============================
   Fetch Project Details
================================*/
$project_query = mysqli_query($conn,"SELECT * FROM projects WHERE id='$project_id'");
$project = mysqli_fetch_assoc($project_query);


/* ===============================
   Total Workers
================================*/
$total_workers_query = mysqli_query($conn,"
    SELECT COUNT(*) as total 
    FROM workers 
    WHERE project_id='$project_id'
");

$total_workers = mysqli_fetch_assoc($total_workers_query)['total'];


/* ===============================
   Present Today
================================*/
$today = date('Y-m-d');

$present_today_query = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM attendance a
    JOIN workers w ON a.worker_id = w.id
    WHERE w.project_id='$project_id'
    AND a.date='$today'
    AND a.status='present'
");

$present_today = mysqli_fetch_assoc($present_today_query)['total'];


/* ===============================
   Monthly Salary Calculation
================================*/
$current_month = date('Y-m');

$salary_query = mysqli_query($conn,"
    SELECT SUM(w.daily_wage) as total_salary
    FROM attendance a
    JOIN workers w ON a.worker_id = w.id
    WHERE w.project_id='$project_id'
    AND a.status='present'
    AND DATE_FORMAT(a.date,'%Y-%m')='$current_month'
");

$salary_data = mysqli_fetch_assoc($salary_query);
$monthly_salary = $salary_data['total_salary'];

if(!$monthly_salary){
    $monthly_salary = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Dashboard | MAA TARA BUILDERS</title>
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
        .project-dashboard {
            width: 100%;
            max-width: 900px;
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

        /* Header section */
        .project-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .project-header::before {
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

        .project-title {
            position: relative;
            z-index: 1;
        }

        .project-title h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .project-meta {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
            backdrop-filter: blur(5px);
        }

        .meta-item i {
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-ongoing {
            background: #f6ad55;
            color: #744210;
        }

        .status-completed {
            background: #48bb78;
            color: #22543d;
        }

        .status-pending {
            background: #4299e1;
            color: #1e4a6b;
        }

        /* Quick stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 30px 40px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: white;
            font-size: 20px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
        }

        /* Action buttons section */
        .actions-section {
            padding: 40px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .section-title i {
            font-size: 24px;
            color: #667eea;
        }

        .section-title h2 {
            font-size: 22px;
            color: #1e293b;
            font-weight: 600;
        }

        .section-title p {
            color: #64748b;
            font-size: 14px;
            margin-left: auto;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 24px;
            padding: 25px 20px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }

        .action-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .action-card:hover::before {
            transform: translateY(0);
        }

        .action-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 28px;
            color: #667eea;
            transition: all 0.3s ease;
        }

        .action-card:hover .action-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(1.1);
        }

        .action-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e293b;
        }

        .action-card p {
            color: #64748b;
            font-size: 13px;
            line-height: 1.4;
        }

        .action-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #f1f5f9;
            color: #475569;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        /* Activity section */
        .recent-activity {
            padding: 30px 40px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .activity-list {
            margin-top: 20px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #667eea;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            color: #1e293b;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .activity-time {
            color: #94a3b8;
            font-size: 12px;
        }

        .view-all {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-top: 15px;
        }

        .view-all:hover {
            gap: 10px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .project-header {
                padding: 30px 20px;
            }

            .project-title h1 {
                font-size: 28px;
            }

            .quick-stats {
                padding: 20px;
                gap: 10px;
            }

            .actions-section {
                padding: 30px 20px;
            }

            .action-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .recent-activity {
                padding: 30px 20px;
            }
        }

        @media (max-width: 480px) {
            .quick-stats {
                grid-template-columns: 1fr;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }

            .project-meta {
                flex-direction: column;
                gap: 10px;
            }

            .meta-item {
                width: 100%;
            }
        }

        /* Loading animation for stats */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .loading .stat-number {
            animation: pulse 1.5s ease-in-out infinite;
        }

        /* Tooltip */
        [data-tooltip] {
            position: relative;
            cursor: help;
        }

        [data-tooltip]:before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 5px 10px;
            background: #1e293b;
            color: white;
            font-size: 12px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        [data-tooltip]:hover:before {
            opacity: 1;
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

    <div class="project-dashboard">
        <!-- Header with project info -->
        <div class="project-header">
            <a href="../dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            
            <div class="project-title">
                <h1><?php echo $project['project_name']; ?></h1>
                
                <div class="project-meta">
                    <span class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo $project['location']; ?>
                    </span>
                    
                    <span class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        Started: <?php echo date('d M Y', strtotime($project['start_date'])); ?>
                    </span>
                    
                    <span class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span class="status-badge status-<?php echo strtolower($project['status']); ?>">
                            <?php echo $project['status']; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick stats -->
<div class="quick-stats">

    <!-- Total Workers -->
    <div class="stat-item">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number"><?php echo $total_workers; ?></div>
        <div class="stat-label">Total Workers</div>
    </div>

    <!-- Present Today -->
    <div class="stat-item">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-number"><?php echo $present_today; ?></div>
        <div class="stat-label">Present Today</div>
    </div>

    <!-- Monthly Salary -->
    <div class="stat-item">
        <div class="stat-icon">
            <i class="fas fa-rupee-sign"></i>
        </div>
        <div class="stat-number">
            ₹ <?php echo number_format($monthly_salary); ?>
        </div>
        <div class="stat-label">Monthly Salary</div>
    </div>

</div>
        <!-- Action buttons section -->
        <div class="actions-section">
            <div class="section-title">
                <i class="fas fa-cog"></i>
                <h2>Project Management</h2>
                </p>
            </div>

            <div class="action-grid">
                <!-- Add Worker -->
                <a href="../worker/add_worker.php?project_id=<?php echo $project_id; ?>" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Add Worker</h3>
                    <p>Register new worker to this project</p>
                    <span class="action-badge">New</span>
                </a>

                <!-- Worker List -->
                <a href="../worker/project_workers.php?project_id=<?php echo $project_id; ?>" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Worker List</h3>
                    <p>View all workers in this project</p>
                    <span class="action-badge">24</span>
                </a>

                <!-- Take Attendance -->
                <a href="../attendance/take_attendance.php?project_id=<?php echo $project_id; ?>" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Take Attendance</h3>
                    <p>Mark today's attendance</p>
                    <span class="action-badge">Today</span>
                </a>

                <!-- View Attendance -->
                <a href="../attendance/view_attendance.php?project_id=<?php echo $project_id; ?>" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>View Attendance</h3>
                    <p>Check attendance records</p>
                    <span class="action-badge">History</span>
                </a>

                <!-- Monthly Report -->
                <a href="../attendance/monthly_report.php?project_id=<?php echo $project_id; ?>" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Monthly Report</h3>
                    <p>View monthly attendance summary</p>
                    <span class="action-badge">PDF</span>
                </a>

                <!-- Salary Report -->
                <a href="../attendance/salary_report.php?project_id=<?php echo $project_id; ?>" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <h3>Salary Report</h3>
                    <p>Generate salary statements</p>
                    <span class="action-badge">Payroll</span>
                </a>
            </div>
        </div>
    <script>
        // Add smooth hover effects
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease';
            });
        });

        // Simulate loading for stats (optional)
        setTimeout(() => {
            document.querySelector('.quick-stats').classList.remove('loading');
        }, 1000);

        // Add click animation
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });
        });

        // Update status badge color based on status
        const statusElement = document.querySelector('.status-badge');
        if(statusElement) {
            const status = statusElement.textContent.trim().toLowerCase();
            statusElement.className = 'status-badge status-' + status;
        }
    </script>
</body>
</html>