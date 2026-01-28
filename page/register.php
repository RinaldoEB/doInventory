<?php 
    include __DIR__ . "/../server/database.php";
    error_reporting(E_ALL);
    ini_set('reporting_errors', 1);

    if(isset($_POST['register'])) {
        $username = mysqli_real_escape_string($db,trim($_POST['username'])); 
        $password = mysqli_real_escape_string($db,trim($_POST['password']));

        $query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
        if(empty($username) || empty($password)) {
            $_SESSION['report_message'] = 'tidak boleh kosong';
        }else {
            if($db->query($query)) {
                $_SESSION['report_message'] = 'Berhasil menambah Data';
            }else {
                $_SESSION['report_message'] = 'Data gagal Dibuat';
            }
        }


        $db->close();
    }



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>REGISTER AKUN</h1>
    <?php if(isset($_SESSION['report_message'])) echo$_SESSION['report_message']; unset($_SESSION['report_message']);?>
    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="register">
    </form>
    <a href="./index.php">Kembali</a>
</body>
</html>