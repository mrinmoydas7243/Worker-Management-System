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

// Get date range for filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$worker_filter = isset($_GET['worker_id']) ? $_GET['worker_id'] : '';

// Build query with filters
$query = "SELECT attendance.*, workers.name, workers.daily_wage 
          FROM attendance
          JOIN workers ON attendance.worker_id = workers.id
          WHERE attendance.project_id='$project_id'";

if(!empty($start_date) && !empty($end_date)) {
    $query .= " AND attendance.date BETWEEN '$start_date' AND '$end_date'";
}

if(!empty($worker_filter)) {
    $query .= " AND attendance.worker_id='$worker_filter'";
}

$query .= " ORDER BY attendance.date DESC, workers.name ASC";

$result = mysqli_query($conn, $query);

// Get workers for filter dropdown
$workers_query = "SELECT id, name FROM workers WHERE project_id='$project_id' ORDER BY name ASC";
$workers_result = mysqli_query($conn, $workers_query);

// Calculate statistics
$stats_query = "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as total_absent,
                SUM(overtime) as total_overtime
                FROM attendance 
                WHERE project_id='$project_id'";

if(!empty($start_date) && !empty($end_date)) {
    $stats_query .= " AND date BETWEEN '$start_date' AND '$end_date'";
}

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance | MAA TARA BUILDERS</title>
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

        .export-btn {
            background: white;
            color: #667eea;
            padding: 12px 24px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filter-group label i {
            color: #667eea;
            font-size: 12px;
        }

        .filter-input {
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .apply-btn {
            padding: 12px 25px;
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
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .reset-btn {
            padding: 12px 20px;
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .reset-btn:hover {
            border-color: #667eea;
            color: #667eea;
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
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #e2e8f0;
        }

        .summary-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .summary-icon.present {
            background: #c6f6d5;
            color: #22543d;
        }

        .summary-icon.absent {
            background: #fed7d7;
            color: #742a2a;
        }

        .summary-icon.overtime {
            background: #bee3f8;
            color: #1e4a6b;
        }

        .summary-info h4 {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .summary-info .value {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Table Section */
        .table-section {
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
            min-width: 800px;
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

        .date-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #475569;
        }

        .date-badge i {
            color: #667eea;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
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

        .work-type-badge {
            background: #e2e8f0;
            color: #475569;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
        }

        .overtime-badge {
            background: #bee3f8;
            color: #1e4a6b;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
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

        .take-attendance-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
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

            .filter-section {
                padding: 20px 20px 0;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .summary-cards {
                margin: 20px;
                grid-template-columns: 1fr;
            }

            .table-section {
                padding: 20px;
            }
        }

        /* Print styles */
        @media print {
            .back-button, .export-btn, .filter-section, .stats-grid, .summary-cards {
                display: none;
            }

            body {
                background: white;
                padding: 0;
            }

            .attendance-container {
                box-shadow: none;
            }

            table {
                border: 1px solid #000;
            }

            th {
                background: #f0f0f0;
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
                            <i class="fas fa-calendar-check"></i>
                            Attendance Report
                        </h1>
                        <div class="project-name">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($project['project_name']); ?>
                        </div>
                    </div>
                    
                    <button class="export-btn" onclick="exportToCSV()">
                        <i class="fas fa-download"></i>
                        Export Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Records</h3>
                    <div class="stat-number"><?php echo $stats['total_records'] ?? 0; ?></div>
                    <div class="stat-sub">Attendance entries</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Date Range</h3>
                    <div class="stat-number"><?php echo date('d M', strtotime($start_date)); ?> - <?php echo date('d M', strtotime($end_date)); ?></div>
                    <div class="stat-sub">Selected period</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>Attendance Rate</h3>
                    <?php 
                    $rate = ($stats['total_records'] > 0) ? round(($stats['total_present'] / $stats['total_records']) * 100) : 0;
                    ?>
                    <div class="stat-number"><?php echo $rate; ?>%</div>
                    <div class="stat-sub">Present vs Total</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-card">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    <span>Filter Attendance Records</span>
                </div>

                <form method="GET" action="" id="filterForm">
                    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                    
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>
                                <i class="fas fa-calendar-start"></i>
                                Start Date
                            </label>
                            <input type="date" name="start_date" class="filter-input" value="<?php echo $start_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="filter-group">
                            <label>
                                <i class="fas fa-calendar-end"></i>
                                End Date
                            </label>
                            <input type="date" name="end_date" class="filter-input" value="<?php echo $end_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="filter-group">
                            <label>
                                <i class="fas fa-user"></i>
                                Worker
                            </label>
                            <select name="worker_id" class="filter-input">
                                <option value="">All Workers</option>
                                <?php while($worker = mysqli_fetch_assoc($workers_result)): ?>
                                <option value="<?php echo $worker['id']; ?>" <?php echo $worker_filter == $worker['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($worker['name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="apply-btn">
                                <i class="fas fa-search"></i>
                                Apply Filters
                            </button>
                            <a href="view_attendance.php?project_id=<?php echo $project_id; ?>" class="reset-btn">
                                <i class="fas fa-undo"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-icon present">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="summary-info">
                    <h4>Total Present</h4>
                    <div class="value"><?php echo $stats['total_present'] ?? 0; ?></div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon absent">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="summary-info">
                    <h4>Total Absent</h4>
                    <div class="value"><?php echo $stats['total_absent'] ?? 0; ?></div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon overtime">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="summary-info">
                    <h4>Total Overtime</h4>
                    <div class="value"><?php echo number_format($stats['total_overtime'] ?? 0, 1); ?> hrs</div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="table-section">
            <div class="table-container">
                <?php if(mysqli_num_rows($result) > 0): ?>
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Worker</th>
                            <th>Status</th>
                            <th>Work Type</th>
                            <th>Overtime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $initial = strtoupper(substr($row['name'], 0, 1));
                        ?>
                        <tr>
                            <td>
                                <div class="date-badge">
                                    <i class="fas fa-calendar-day"></i>
                                    <?php echo date('d M Y', strtotime($row['date'])); ?>
                                </div>
                            </td>
                            <td>
                                <div class="worker-info">
                                    <div class="worker-avatar">
                                        <?php echo $initial; ?>
                                    </div>
                                    <span><?php echo htmlspecialchars($row['name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <i class="fas fa-<?php echo $row['status'] == 'Present' ? 'check-circle' : 'times-circle'; ?>"></i>
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if(!empty($row['work_type'])): ?>
                                <span class="work-type-badge">
                                    <i class="fas fa-<?php echo $row['work_type'] == 'Full Day' ? 'sun' : 'clock'; ?>"></i>
                                    <?php echo $row['work_type']; ?>
                                </span>
                                <?php else: ?>
                                <span class="work-type-badge">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['overtime'] > 0): ?>
                                <span class="overtime-badge">
                                    <i class="fas fa-hourglass-half"></i>
                                    <?php echo $row['overtime']; ?> hrs
                                </span>
                                <?php else: ?>
                                <span class="work-type-badge">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Attendance Records Found</h3>
                    <p>No attendance records match your criteria. Try adjusting your filters or take attendance for today.</p>
                    <a href="take_attendance.php?project_id=<?php echo $project_id; ?>" class="take-attendance-btn">
                        <i class="fas fa-plus-circle"></i>
                        Take Attendance
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Export to CSV
        function exportToCSV() {
            const table = document.getElementById('attendanceTable');
            if(!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            // Add headers
            const headers = ['Date', 'Worker', 'Status', 'Work Type', 'Overtime (Hours)'];
            csv.push(headers.join(','));
            
            // Add data rows
            rows.forEach((row, index) => {
                if(index === 0) return; // Skip header row
                const cols = row.querySelectorAll('td');
                const rowData = [
                    '"' + cols[0]?.textContent.trim() + '"',
                    '"' + cols[1]?.textContent.trim() + '"',
                    '"' + cols[2]?.textContent.trim() + '"',
                    '"' + cols[3]?.textContent.trim() + '"',
                    cols[4]?.textContent.trim()
                ];
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'attendance_report_<?php echo date('Y-m-d'); ?>.csv';
            link.click();
        }

        // Validate date range
        document.getElementById('filterForm')?.addEventListener('submit', function(e) {
            const startDate = new Date(this.start_date.value);
            const endDate = new Date(this.end_date.value);
            
            if(startDate > endDate) {
                e.preventDefault();
                alert('Start date cannot be after end date');
            }
        });

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

        // Quick date range filters
        function setDateRange(days) {
            const end = new Date();
            const start = new Date();
            start.setDate(start.getDate() - days);
            
            document.querySelector('input[name="start_date"]').value = start.toISOString().split('T')[0];
            document.querySelector('input[name="end_date"]').value = end.toISOString().split('T')[0];
            
            document.getElementById('filterForm').submit();
        }

        // Add quick filter buttons
        const filterActions = document.querySelector('.filter-actions');
        if(filterActions) {
            const quickFilters = document.createElement('div');
            quickFilters.className = 'quick-filters';
            quickFilters.style.cssText = 'display: flex; gap: 10px; margin-top: 15px;';
            quickFilters.innerHTML = `
                <button type="button" class="reset-btn" onclick="setDateRange(7)" style="padding: 8px 15px;">
                    <i class="fas fa-clock"></i> Last 7 Days
                </button>
                <button type="button" class="reset-btn" onclick="setDateRange(30)" style="padding: 8px 15px;">
                    <i class="fas fa-calendar"></i> Last 30 Days
                </button>
                <button type="button" class="reset-btn" onclick="setDateRange(90)" style="padding: 8px 15px;">
                    <i class="fas fa-calendar-alt"></i> Last 90 Days
                </button>
            `;
            filterActions.parentElement.appendChild(quickFilters);
        }
    </script>
</body>
</html>