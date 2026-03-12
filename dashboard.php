<?php
session_start();
include("config/db.php");

if(!isset($_SESSION['admin']))
{
    header("Location: login.php");
    exit();
}

// Get counts for stats
$total_projects = mysqli_query($conn,"SELECT COUNT(*) as count FROM projects");
$total_projects = mysqli_fetch_assoc($total_projects)['count'];

$active_projects = mysqli_query($conn,"SELECT COUNT(*) as count FROM projects WHERE status='active'");
$active_projects = mysqli_fetch_assoc($active_projects)['count'];

$completed_projects = mysqli_query($conn,"SELECT COUNT(*) as count FROM projects WHERE status='completed'");
$completed_projects = mysqli_fetch_assoc($completed_projects)['count'];

// Get projects with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$result = mysqli_query($conn,"SELECT * FROM projects LIMIT $offset, $limit");
$total_records = mysqli_query($conn,"SELECT COUNT(*) as count FROM projects");
$total_records = mysqli_fetch_assoc($total_records)['count'];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MAA TARA BUILDERS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: #f8fafc;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 30px;
        }

        .sidebar-header h2 {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 1px;
            line-height: 1.3;
        }

        .sidebar-header .subtitle {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 5px;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            gap: 12px;
        }

        .nav-link i {
            width: 20px;
            font-size: 18px;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }

        .nav-link.logout {
            margin-top: 30px;
            background: rgba(231, 76, 60, 0.2);
        }

        .nav-link.logout:hover {
            background: rgba(231, 76, 60, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .page-title h1 {
            font-size: 28px;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .page-title p {
            color: #64748b;
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-outline {
            background: white;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-info h3 {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #34b1aa 0%, #2c7a7b 100%);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #f6ad55 0%, #dd6b20 100%);
        }

        /* Table Section */
        .table-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .table-header h2 {
            font-size: 20px;
            color: #1e293b;
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            padding: 12px 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            width: 300px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .table-container {
            overflow-x: auto;
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
            padding: 16px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 14px;
        }

        tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
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

        .action-btn {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
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
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .page-link:hover, .page-link.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            
            .sidebar-header h2, .sidebar-header .subtitle, .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .nav-link {
                justify-content: center;
            }
            
            .nav-link i {
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .search-box input {
                width: 200px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>MAA TARA<br>BUILDERS</h2>
                <div class="subtitle">Admin Dashboard</div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="page-title">
                    <h1>Projects Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin']); ?>!</p>
                </div>
                <div class="header-actions">
                    <a href="projects/add_project.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Project
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Projects</h3>
                        <div class="stat-number"><?php echo $total_projects; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Active Projects</h3>
                        <div class="stat-number"><?php echo $active_projects; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Completed</h3>
                        <div class="stat-number"><?php echo $completed_projects; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Projects Table -->
            <div class="table-section">
                <div class="table-header">
                    <h2>All Projects</h2>
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search projects..." onkeyup="searchTable()">
                        <button class="btn btn-outline">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table id="projectsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Project Name</th>
                                <th>Location</th>
                                <th>Start Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)) { 
                                $status_class = '';
                                switch(strtolower($row['status'])) {
                                    case 'active':
                                        $status_class = 'status-active';
                                        break;
                                    case 'completed':
                                        $status_class = 'status-completed';
                                        break;
                                    default:
                                        $status_class = 'status-pending';
                                }
                            ?>
                            <tr>
                                <td>#<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['project_name']); ?></strong></td>
                                <td><i class="fas fa-map-marker-alt" style="color: #667eea; margin-right: 5px;"></i><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['start_date'])); ?></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <a href="projects/project_page.php?id=<?php echo $row['id']; ?>" class="action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    <a href="projects/update_status.php?id=<?php echo $row['id']; ?>" class="action-btn" style="background:#f59e0b;">
                                        <i class="fas fa-edit"></i> Update Status
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('projectsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const projectName = row.getElementsByTagName('td')[1];
                const location = row.getElementsByTagName('td')[2];
                
                if (projectName || location) {
                    const nameValue = projectName.textContent || projectName.innerText;
                    const locationValue = location.textContent || location.innerText;
                    
                    if (nameValue.toLowerCase().indexOf(filter) > -1 || 
                        locationValue.toLowerCase().indexOf(filter) > -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        }

        // Optional: Add smooth transitions
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if(this.classList.contains('logout')) return;
                e.preventDefault();
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>