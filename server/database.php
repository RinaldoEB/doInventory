<?php
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "db_makan";

    $db = mysqli_connect($hostname, $username, $password, $database);

    if($db->connect_error){
        die("Koneksi Gagal: " . $db->connect_error);
    }

?>
