<?php
session_start();
include("../config/db.php");

$date = date("Y-m-d");
$project_id = $_POST['project_id'];

foreach($_POST['worker_id'] as $worker_id)
{
    $status = $_POST['status'][$worker_id];
    $worktype = $_POST['worktype'][$worker_id] ?? NULL;
    $overtime = $_POST['overtime'][$worker_id] ?? 0;

    $query = "INSERT INTO attendance(project_id,worker_id,date,status,work_type,overtime)
              VALUES('$project_id','$worker_id','$date','$status','$worktype','$overtime')";

    mysqli_query($conn,$query);
}

header("Location: view_attendance.php?project_id=".$project_id);
?>