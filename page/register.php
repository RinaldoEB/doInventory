<?php 
    session_start();
    include __DIR__ . "/../server/database.php";
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if(isset($_POST['register'])) {
        $username = trim($_POST['username']); 
        $password = trim($_POST['password']);
        
        if(empty($username) || empty($password)) {
            $_SESSION['report_message'] = 'tidak boleh kosong';
            exit;
        }

        $hash = password_hash($password,PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, password_hash) VALUES (?,?)";
        $stmt = $db->prepare($query);

        if (!$stmt) {
            $_SESSION['report_message'] = 'Prepare gagal';
            exit;
        }
        $stmt->bind_param("ss",$username,$hash);

        if($stmt->execute()) {
            $_SESSION['report_message'] = 'Akun berhasil ditambah';
        }else {
            if($db->errno === 1062) {
                $_SESSION['report_message'] = 'Username sudah terdaftar';
            } else {
                $_SESSION['report_message'] = 'Akun gagal dibuat';
            }
        }


        $stmt->close();
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