<?php
require 'config.php';
if (is_logged_in()) { header('Location: index.php'); exit; }
$pageTitle = 'Log in';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        set_flash('success', 'Welcome back, ' . $user['name'] . '.');
        header('Location: index.php');
        exit;
    }
    $error = 'Incorrect email or password.';
}

require 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white border border-stone-300 rounded-md p-6 sm:p-8">
    <h1 class="text-2xl font-bold">Log in</h1>
    <p class="mt-1 text-stone-600 text-sm">Log in to post items and manage your listings.</p>

    <?php if ($error): ?>
        <div class="mt-5 bg-red-50 border border-red-300 text-red-800 text-sm rounded-md px-4 py-3"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-4">
        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" name="email" type="email" value="<?= e($email) ?>" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input id="password" name="password" type="password" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
        </div>
        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-teal-800 hover:bg-teal-900 text-white font-medium px-4 py-2.5 rounded-md"><?= icon('log-in', 'w-4 h-4') ?> Log in</button>
    </form>

    <p class="mt-5 text-sm text-stone-600">No account yet? <a href="register.php" class="text-teal-800 font-medium underline">Create one</a></p>
</div>

<?php require 'includes/footer.php'; ?>
