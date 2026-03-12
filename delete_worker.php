<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['id']) && isset($_GET['project_id']))
{
    $id = $_GET['id'];
    $project_id = $_GET['project_id'];

    $query = "DELETE FROM workers WHERE id='$id'";
    
    if(mysqli_query($conn, $query)){
        header("Location: project_workers.php?project_id=$project_id");
        exit();
    } else {
        echo "Error deleting worker";
    }
}
else{
    echo "Invalid request";
}
?>