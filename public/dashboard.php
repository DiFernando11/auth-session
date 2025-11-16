<?php
session_start();

if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../src/db.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    $userId = $user['id'];
    $userName = $user['name'];
    $userEmail = $user['email'];
    
} catch (PDOException $e) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Auth System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex flex-col">
    <header class="bg-[#111827] shadow-lg border-b border-gray-700">
        <div class="max-w-4xl mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-white">Dashboard</h1>
                    <p class="text-sm text-gray-400">Welcome back, <?= htmlspecialchars($userName) ?>!</p>
                </div>
                <nav role="navigation" aria-label="User navigation">
                    <a href="dashboard.php?logout=1" 
                       class="bg-red-600 hover:bg-red-700 transition-colors px-4 py-2 rounded-lg font-medium text-white"
                       aria-label="Logout from your account"
                       onclick="return confirm('Are you sure you want to logout?');">
                        Logout
                    </a>
                </nav>
            </div>
        </div>
    </header>
    <main class="flex-1 max-w-4xl mx-auto px-6 py-8 w-full">
    
        <section class="bg-[#111827] rounded-xl shadow-xl p-8 mb-8" aria-labelledby="welcome-heading">
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-green-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-white">
                        <?= strtoupper(substr(htmlspecialchars($userName), 0, 1)) ?>
                    </span>
                </div>
                <h2 id="welcome-heading" class="text-3xl font-bold mb-2">Welcome, <?= htmlspecialchars($userName) ?>!</h2>
                <p class="text-gray-400">You have successfully logged into your account</p>
            </div>
        </section>
        <section class="bg-[#111827] rounded-xl shadow-xl p-8 mb-8" aria-labelledby="user-info-heading">
            <h3 id="user-info-heading" class="text-xl font-bold mb-6 flex items-center">
                <span class="w-6 h-6 bg-green-700 rounded-full flex items-center justify-center mr-3" aria-hidden="true">
                    <span class="text-xs font-bold">i</span>
                </span>
                Your Information
            </h3>
            <dl class="grid md:grid-cols-2 gap-6">
                <div class="bg-gray-800 rounded-lg p-4">
                    <dt class="block text-sm font-medium text-gray-400 mb-1">User ID</dt>
                    <dd class="text-lg font-semibold">#<?= htmlspecialchars($userId) ?></dd>
                </div>
                <div class="bg-gray-800 rounded-lg p-4">
                    <dt class="block text-sm font-medium text-gray-400 mb-1">Full Name</dt>
                    <dd class="text-lg font-semibold"><?= htmlspecialchars($userName) ?></dd>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 md:col-span-2">
                    <dt class="block text-sm font-medium text-gray-400 mb-1">Email Address</dt>
                    <dd class="text-lg font-semibold"><?= htmlspecialchars($userEmail) ?></dd>
                </div>
            </dl>
        </section>
    </main>
    <footer class="bg-[#111827] border-t border-gray-700 mt-auto">
        <div class="max-w-4xl mx-auto px-6 py-6">
            <div class="text-center text-sm text-gray-400">
                <p>&copy; 2025 Auth System. Secure session management.</p>
                <p class="mt-1">Session active since login</p>
            </div>
        </div>
    </footer>

</body>
</html>