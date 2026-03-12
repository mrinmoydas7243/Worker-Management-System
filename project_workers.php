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

// Get total monthly wage
$wage_query = "SELECT SUM(daily_wage * 26) as monthly_total FROM workers WHERE project_id='$project_id'";
$wage_result = mysqli_query($conn, $wage_query);
$monthly_total = mysqli_fetch_assoc($wage_result)['monthly_total'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM workers WHERE project_id='$project_id' LIMIT $offset, $limit";
$result = mysqli_query($conn, $query);

$total_pages = ceil($total_workers / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Workers | MAA TARA BUILDERS</title>
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
        .workers-container {
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

        .add-worker-btn {
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .add-worker-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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

        .stat-sub {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* Search and filter bar */
        .action-bar {
            padding: 30px 40px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-box input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 10px 20px;
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
        }

        .filter-btn:hover, .filter-btn.active {
            border-color: #667eea;
            color: #667eea;
        }

        /* Table section */
        .table-section {
            padding: 30px 40px;
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

        .worker-id {
            font-weight: 600;
            color: #667eea;
        }

        .worker-name {
            font-weight: 500;
            color: #1e293b;
        }

        .worker-name i {
            color: #94a3b8;
            margin-right: 8px;
        }

        .phone-number {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
        }

        .phone-number i {
            color: #667eea;
        }

        .wage-badge {
            background: #c6f6d5;
            color: #22543d;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            color: white;
        }

        .edit-btn {
            background: #4299e1;
        }

        .edit-btn:hover {
            background: #3182ce;
            transform: translateY(-2px);
        }

        .delete-btn {
            background: #f56565;
        }

        .delete-btn:hover {
            background: #e53e3e;
            transform: translateY(-2px);
        }

        .view-btn {
            background: #667eea;
        }

        .view-btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
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

        .empty-state .add-first-btn {
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

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        .page-link {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #64748b;
            transition: all 0.3s ease;
            min-width: 40px;
            text-align: center;
        }

        .page-link:hover, .page-link.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
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

            .action-bar {
                padding: 20px 20px 0;
            }

            .table-section {
                padding: 20px;
            }

            .filter-buttons {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 10px;
            }

            .filter-btn {
                white-space: nowrap;
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

    <div class="workers-container">
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
                            <i class="fas fa-users"></i>
                            Project Workers
                        </h1>
                        <div class="project-name">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($project['project_name']); ?>
                        </div>
                    </div>
                    
                    <a href="add_worker.php?project_id=<?php echo $project_id; ?>" class="add-worker-btn">
                        <i class="fas fa-plus-circle"></i>
                        Add New Worker
                    </a>
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
                    <div class="stat-sub">Registered workers</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>Monthly Wage Bill</h3>
                    <div class="stat-number">₹ <?php echo number_format($monthly_total); ?></div>
                    <div class="stat-sub">Approx. monthly expense</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>Average Daily Wage</h3>
                    <?php
                    $avg_query = "SELECT AVG(daily_wage) as avg FROM workers WHERE project_id='$project_id'";
                    $avg_result = mysqli_query($conn, $avg_query);
                    $avg_wage = mysqli_fetch_assoc($avg_result)['avg'];
                    ?>
                    <div class="stat-number">₹ <?php echo number_format($avg_wage); ?></div>
                    <div class="stat-sub">Per worker</div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="action-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search workers by name or phone..." onkeyup="searchTable()">
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterWorkers('all')">
                    <i class="fas fa-list"></i> All
                </button>
                <button class="filter-btn" onclick="filterWorkers('wage-high')">
                    <i class="fas fa-arrow-up"></i> High Wage
                </button>
                <button class="filter-btn" onclick="filterWorkers('wage-low')">
                    <i class="fas fa-arrow-down"></i> Low Wage
                </button>
            </div>
        </div>

        <!-- Workers Table -->
        <div class="table-section">
            <div class="table-container">
                <?php if(mysqli_num_rows($result) > 0): ?>
                <table id="workersTable">
                    <thead>
                        <tr>
                            <th>Worker ID</th>
                            <th>Worker Name</th>
                            <th>Phone Number</th>
                            <th>Joining Date</th>
                            <th>Daily Wage</th>
                            <th>Monthly Wage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $monthly = $row['daily_wage'] * 26;
                        ?>
                        <tr>
                            <td>
                                <span class="worker-id">#<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></span>
                            </td>
                            <td>
                                <span class="worker-name">
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="phone-number">
                                    <i class="fas fa-phone"></i>
                                    +91 <?php echo $row['phone']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="phone-number">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date("d M Y", strtotime($row['joining_date'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="wage-badge">
                                    <i class="fas fa-rupee-sign"></i>
                                    <?php echo number_format($row['daily_wage']); ?>/day
                                </span>
                            </td>
                            <td>
                                <span class="wage-badge" style="background: #bee3f8; color: #1e4a6b;">
                                    <i class="fas fa-calendar"></i>
                                    ₹ <?php echo number_format($monthly); ?>/month
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_worker.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn" title="Edit Worker">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_worker.php?id=<?php echo $row['id']; ?>&project_id=<?php echo $project_id; ?>" 
                                        class="action-btn delete-btn"
                                        title="Delete Worker"
                                        onclick="return confirm('Are you sure you want to delete this worker?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h3>No Workers Found</h3>
                    <p>Start by adding workers to this project</p>
                    <a href="add_worker.php?project_id=<?php echo $project_id; ?>" class="add-first-btn">
                        <i class="fas fa-plus-circle"></i>
                        Add Your First Worker
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?project_id=<?php echo $project_id; ?>&page=<?php echo $i; ?>" 
                       class="page-link <?php echo $page == $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Search functionality
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('workersTable');
            
            if(!table) return;
            
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const name = row.getElementsByTagName('td')[1];
                const phone = row.getElementsByTagName('td')[2];
                
                if (name || phone) {
                    const nameValue = name.textContent || name.innerText;
                    const phoneValue = phone.textContent || phone.innerText;
                    
                    if (nameValue.toLowerCase().indexOf(filter) > -1 || 
                        phoneValue.toLowerCase().indexOf(filter) > -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        }

        // Filter functionality
        function filterWorkers(type) {
            const table = document.getElementById('workersTable');
            if(!table) return;
            
            const rows = table.getElementsByTagName('tr');
            
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Convert to array for sorting
            const rowsArray = Array.from(rows).slice(1);
            
            if(type === 'wage-high') {
                rowsArray.sort((a, b) => {
                    const wageA = parseInt(a.getElementsByTagName('td')[3].textContent.replace(/[^0-9]/g, ''));
                    const wageB = parseInt(b.getElementsByTagName('td')[3].textContent.replace(/[^0-9]/g, ''));
                    return wageB - wageA;
                });
            } else if(type === 'wage-low') {
                rowsArray.sort((a, b) => {
                    const wageA = parseInt(a.getElementsByTagName('td')[3].textContent.replace(/[^0-9]/g, ''));
                    const wageB = parseInt(b.getElementsByTagName('td')[3].textContent.replace(/[^0-9]/g, ''));
                    return wageA - wageB;
                });
            }
            
            // Reorder rows
            const tbody = table.getElementsByTagName('tbody')[0];
            rowsArray.forEach(row => tbody.appendChild(row));
        }

        // Real-time search with debounce
        let searchTimeout;
        document.getElementById('searchInput')?.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchTable();
            }, 300);
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

        // Confirm delete
        function confirmDelete(workerId) {
            if(confirm('Are you sure you want to delete this worker? This action cannot be undone.')) {
                window.location.href = 'delete_worker.php?id=' + workerId;
            }
        }

        // Export functionality (optional)
        function exportToCSV() {
            const table = document.getElementById('workersTable');
            if(!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                cols.forEach(col => {
                    rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'workers_list.csv';
            a.click();
        }
    </script>
</body>
</html>