<?php 
    session_start();
    include __DIR__ . "/../server/database.php";
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    if(isset($_POST['login'])){
        $username = trim($_POST['username']); 
        $password = trim($_POST['password']); 
        $query = "SELECT id_user, username, password_hash FROM users WHERE username = ? LIMIT 1";
        $stmt = $db->prepare($query);
        
        if(!$stmt) {
            die("Prepare Gagal !");
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1) {
            $data = $result->fetch_assoc();

            if(password_verify($password, $data['password'])) {
                $_SESSION['username'] = $data['username'];  
                $_SESSION['report_message'] = 'login berhasil';
                header("location: dashboard.php");
                exit();
            }

        }else {
            $_SESSION['report_message'] = 'data tidak ada';
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
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login Page</title>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6 uppercase tracking-tight">
            Login Bang
        </h1>

        <?php if(isset($_SESSION['report_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['report_message']; ?></span>
            </div>
            <?php unset($_SESSION['report_message']); ?>
        <?php endif; ?>

        <form action="index.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Masukkan Username" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                >
            </div>

            <button 
                type="submit" 
                name="login"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors duration-300 shadow-md hover:shadow-lg mt-2"
            >
                Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Belum punya akun? 
                <a href="../page/register.php" class="text-blue-600 hover:underline font-medium">Daftar sekarang</a>
            </p>
        </div>
    </div>

</body>
</html>