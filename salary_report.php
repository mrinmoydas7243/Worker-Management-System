<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin']))
{
    header("Location: ../login.php");
    exit();
}

$project_id = $_GET['project_id'] ?? 0;
$month = $_GET['month'] ?? date("Y-m");

// Get project details
$project_query = "SELECT project_name FROM projects WHERE id='$project_id'";
$project_result = mysqli_query($conn, $project_query);
$project = mysqli_fetch_assoc($project_result);

// Parse month for display
$month_display = date('F Y', strtotime($month . '-01'));

// Get workers with their actual daily wages from database
$workers_query = "SELECT id, name, daily_wage FROM workers WHERE project_id='$project_id'";
$workers_result = mysqli_query($conn, $workers_query);
$workers = [];
while($worker = mysqli_fetch_assoc($workers_result)) {
    $workers[$worker['id']] = $worker;
}

// Build salary query with actual wages
$query = "SELECT 
            attendance.worker_id,
            workers.name,
            workers.daily_wage,
            SUM(CASE WHEN attendance.status='Present' AND attendance.work_type='Full Day' THEN 1 ELSE 0 END) AS full_days,
            SUM(CASE WHEN attendance.status='Present' AND attendance.work_type='Half Day' THEN 1 ELSE 0 END) AS half_days,
            SUM(attendance.overtime) AS overtime_hours
          FROM attendance
          JOIN workers ON attendance.worker_id = workers.id
          WHERE attendance.project_id='$project_id'
          AND DATE_FORMAT(attendance.date,'%Y-%m')='$month'
          GROUP BY attendance.worker_id
          ORDER BY workers.name ASC";

$result = mysqli_query($conn, $query);

// Calculate summary statistics
$summary_query = "SELECT 
                    COUNT(DISTINCT worker_id) as total_workers,
                    SUM(CASE WHEN attendance.status='Present' AND attendance.work_type='Full Day' THEN 1 ELSE 0 END) as total_full_days,
                    SUM(CASE WHEN attendance.status='Present' AND attendance.work_type='Half Day' THEN 1 ELSE 0 END) as total_half_days,
                    SUM(attendance.overtime) as total_overtime
                  FROM attendance
                  WHERE attendance.project_id='$project_id'
                  AND DATE_FORMAT(attendance.date,'%Y-%m')='$month'";

$summary_result = mysqli_query($conn, $summary_query);
$summary = mysqli_fetch_assoc($summary_result);

// Get previous and next month for navigation
$current = new DateTime($month . '-01');
$prev_month = clone $current;
$prev_month->modify('-1 month');
$next_month = clone $current;
$next_month->modify('+1 month');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Report | MAA TARA BUILDERS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .report-container {
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

        .month-navigation {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 20px;
            border-radius: 50px;
        }

        .nav-btn {
            color: white;
            text-decoration: none;
            font-size: 18px;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .current-month {
            font-weight: 600;
            font-size: 16px;
        }

        /* Stats Cards */
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

        .stat-sub {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* Chart Section */
        .chart-section {
            padding: 30px 40px;
            border-bottom: 1px solid #e2e8f0;
        }

        .chart-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #1e293b;
        }

        .chart-title i {
            color: #667eea;
            font-size: 20px;
        }

        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .chart-box {
            background: #f8fafc;
            padding: 20px;
            border-radius: 20px;
            height: 300px;
        }

        /* Filter Section */
        .filter-section {
            padding: 30px 40px 0;
        }

        .filter-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid #e2e8f0;
        }

        .filter-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #1e293b;
            font-weight: 600;
        }

        .filter-title i {
            color: #667eea;
        }

        .filter-form {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filter-group label i {
            color: #667eea;
        }

        .filter-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        .generate-btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            height: 45px;
        }

        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 30px 40px 0;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }

        .summary-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        .summary-total {
            font-size: 28px;
            font-weight: 700;
            color: #047857;
        }

        /* Table Section */
        .table-section {
            padding: 30px 40px 40px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-title h2 {
            font-size: 20px;
            color: #1e293b;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
        }

        .export-btn {
            padding: 12px 24px;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .export-btn:hover {
            background: #667eea;
            color: white;
        }

        .print-btn {
            padding: 12px 24px;
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .print-btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
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
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-weight: 600;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-primary {
            background: #c6f6d5;
            color: #22543d;
        }

        .badge-warning {
            background: #feebc8;
            color: #744210;
        }

        .badge-info {
            background: #bee3f8;
            color: #1e4a6b;
        }

        .salary-cell {
            font-weight: 700;
            color: #047857;
            font-size: 16px;
        }

        .wage-cell {
            font-weight: 600;
            color: #4b5563;
        }

        .total-row {
            background: #f8fafc;
            font-weight: 600;
        }

        .total-row td {
            border-top: 2px solid #e2e8f0;
            font-weight: 700;
        }

        .grand-total {
            font-size: 18px;
            color: #047857;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
        }

        .empty-state i {
            font-size: 80px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 20px;
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

            .chart-container {
                grid-template-columns: 1fr;
            }

            .summary-cards {
                grid-template-columns: 1fr;
                margin: 20px;
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .stats-grid, .chart-section, .filter-section, .table-section {
                padding: 20px;
            }

            .export-buttons {
                flex-direction: column;
                width: 100%;
            }
        }

        /* Print styles */
        @media print {
            .back-button, .month-navigation, .filter-section, .export-buttons, .chart-section {
                display: none;
            }

            body {
                background: white;
                padding: 0;
            }

            .report-container {
                box-shadow: none;
            }

            .salary-cell {
                color: black;
            }
        }
    </style>
</head>
<body>
    <!-- Animated background -->
    <div class="bg-pattern">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
    </div>

    <div class="report-container">
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
                            <i class="fas fa-rupee-sign"></i>
                            Salary Report
                        </h1>
                        <div class="project-name">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($project['project_name'] ?? 'Project'); ?>
                        </div>
                    </div>
                    
                    <div class="month-navigation">
                        <a href="?project_id=<?php echo $project_id; ?>&month=<?php echo $prev_month->format('Y-m'); ?>" class="nav-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <span class="current-month"><?php echo $month_display; ?></span>
                        <a href="?project_id=<?php echo $project_id; ?>&month=<?php echo $next_month->format('Y-m'); ?>" class="nav-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
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
                    <h3>Workers Paid</h3>
                    <div class="stat-number"><?php echo mysqli_num_rows($result); ?></div>
                    <div class="stat-sub">This month</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Days</h3>
                    <?php $total_days = ($summary['total_full_days'] ?? 0) + ($summary['total_half_days'] ?? 0); ?>
                    <div class="stat-number"><?php echo $total_days; ?></div>
                    <div class="stat-sub">Working days</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Overtime Hours</h3>
                    <div class="stat-number"><?php echo number_format($summary['total_overtime'] ?? 0, 1); ?></div>
                    <div class="stat-sub">Extra hours</div>
                </div>
            </div>
        </div>
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-card">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    <span>Select Month</span>
                </div>

                <form method="GET" class="filter-form">
                    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                    
                    <div class="filter-group">
                        <label>
                            <i class="fas fa-calendar-alt"></i>
                            Month
                        </label>
                        <input type="month" name="month" class="filter-input" value="<?php echo $month; ?>" max="<?php echo date('Y-m'); ?>">
                    </div>

                    <button type="submit" class="generate-btn">
                        <i class="fas fa-calculator"></i>
                        Calculate Salary
                    </button>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list"></i>
                    <h2>Salary Details</h2>
                </div>
                <div class="export-buttons">
                    <button class="print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        Print
                    </button>
                    <button class="export-btn" onclick="exportToCSV()">
                        <i class="fas fa-download"></i>
                        Export CSV
                    </button>
                </div>
            </div>

            <div class="table-container">
                <?php if(mysqli_num_rows($result) > 0): ?>
                <table id="salaryTable">
                    <thead>
                        <tr>
                            <th>Worker</th>
                            <th>Daily Wage</th>
                            <th>Full Days</th>
                            <th>Half Days</th>
                            <th>Overtime (hrs)</th>
                            <th>Full Day Salary</th>
                            <th>Half Day Salary</th>
                            <th>Overtime Pay</th>
                            <th>Total Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        $total_full_days = 0;
                        $total_half_days = 0;
                        $total_overtime = 0;
                        
                        while($row = mysqli_fetch_assoc($result)): 
                            $initial = strtoupper(substr($row['name'], 0, 1));
                            
                            // Calculations
                            $full_salary = $row['full_days'] * $row['daily_wage'];
                            $half_salary = $row['half_days'] * ($row['daily_wage'] / 2);
                            $overtime_salary = $row['overtime_hours'] * ($row['daily_wage'] / 8); // Assuming overtime at same hourly rate
                            $total_salary = $full_salary + $half_salary + $overtime_salary;
                            
                            $grand_total += $total_salary;
                            $total_full_days += $row['full_days'];
                            $total_half_days += $row['half_days'];
                            $total_overtime += $row['overtime_hours'];
                        ?>
                        <tr>
                            <td>
                                <div class="worker-info">
                                    <div class="worker-avatar">
                                        <?php echo $initial; ?>
                                    </div>
                                    <span><?php echo htmlspecialchars($row['name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="wage-cell">₹ <?php echo number_format($row['daily_wage']); ?></span>
                            </td>
                            <td>
                                <span class="badge badge-primary">
                                    <i class="fas fa-sun"></i> <?php echo $row['full_days']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock"></i> <?php echo $row['half_days']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['overtime_hours'] > 0): ?>
                                <span class="badge badge-info">
                                    <i class="fas fa-hourglass-half"></i> <?php echo number_format($row['overtime_hours'], 1); ?>
                                </span>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td>₹ <?php echo number_format($full_salary); ?></td>
                            <td>₹ <?php echo number_format($half_salary); ?></td>
                            <td>₹ <?php echo number_format($overtime_salary); ?></td>
                            <td class="salary-cell">₹ <?php echo number_format($total_salary); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2" style="text-align: right; font-weight: 600;">Totals:</td>
                            <td><strong><?php echo $total_full_days; ?></strong></td>
                            <td><strong><?php echo $total_half_days; ?></strong></td>
                            <td><strong><?php echo number_format($total_overtime, 1); ?></strong></td>
                            <td colspan="3" style="text-align: right; font-weight: 600;">Grand Total:</td>
                            <td class="grand-total">₹ <?php echo number_format($grand_total); ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calculator"></i>
                    <h3>No Salary Data Available</h3>
                    <p>No attendance records found for <?php echo $month_display; ?>. Take attendance first to generate salary reports.</p>
                    <a href="take_attendance.php?project_id=<?php echo $project_id; ?>" class="generate-btn" style="display: inline-flex;">
                        <i class="fas fa-plus-circle"></i>
                        Take Attendance
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Get data for charts
            const workers = [];
            const salaries = [];
            
            <?php 
            mysqli_data_seek($result, 0);
            while($row = mysqli_fetch_assoc($result)): 
                $full_salary = $row['full_days'] * $row['daily_wage'];
                $half_salary = $row['half_days'] * ($row['daily_wage'] / 2);
                $overtime_salary = $row['overtime_hours'] * ($row['daily_wage'] / 8);
                $total_salary = $full_salary + $half_salary + $overtime_salary;
            ?>
            workers.push('<?php echo addslashes($row['name']); ?>');
            salaries.push(<?php echo $total_salary; ?>);
            <?php endwhile; ?>

            // Salary Distribution Chart
            const salaryCtx = document.getElementById('salaryChart')?.getContext('2d');
            if(salaryCtx && workers.length > 0) {
                new Chart(salaryCtx, {
                    type: 'bar',
                    data: {
                        labels: workers.slice(0, 5), // Show top 5 workers
                        datasets: [{
                            label: 'Salary (₹)',
                            data: salaries.slice(0, 5),
                            backgroundColor: '#667eea',
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Work Type Distribution Chart
            const workTypeCtx = document.getElementById('workTypeChart')?.getContext('2d');
            if(workTypeCtx) {
                new Chart(workTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Full Day', 'Half Day', 'Overtime'],
                        datasets: [{
                            data: [
                                <?php echo $summary['total_full_days'] ?? 0; ?>,
                                <?php echo $summary['total_half_days'] ?? 0; ?>,
                                <?php echo number_format($summary['total_overtime'] ?? 0, 1); ?>
                            ],
                            backgroundColor: ['#10b981', '#f59e0b', '#3b82f6'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });

        // Export to CSV
        function exportToCSV() {
            const table = document.getElementById('salaryTable');
            if(!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                cols.forEach(col => {
                    let text = col.innerText.trim();
                    // Remove icons and extra spaces
                    text = text.replace(/[^\x20-\x7E₹]/g, '').trim();
                    rowData.push('"' + text + '"');
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' }); // Add BOM for Excel
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'salary_report_<?php echo $month; ?>.csv';
            link.click();
        }

        // Add animation to table rows
        document.querySelectorAll('tbody tr').forEach((row, index) => {
            row.style.animation = `fadeIn 0.3s ease-out ${index * 0.05}s forwards`;
            row.style.opacity = '0';
        });

        // Add fadeIn animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateX(-10px); }
                to { opacity: 1; transform: translateX(0); }
            }
        `;
        document.head.appendChild(style);

        // Format numbers as currency
        function formatCurrency(amount) {
            return '₹' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    </script>
</body>
</html>