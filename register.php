<?php
require 'config.php';
if (is_logged_in()) { header('Location: index.php'); exit; }
$pageTitle = 'Create account';

$errors = [];
$name = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($phone !== '' && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) $errors[] = 'Phone number format is not valid.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'That email is already registered.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $phone, $hash);
        $stmt->execute();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $name;
        set_flash('success', 'Account created. Welcome to Re-Use.');
        header('Location: index.php');
        exit;
    }
}

require 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white border border-stone-300 rounded-md p-6 sm:p-8">
    <h1 class="text-2xl font-bold">Create your account</h1>
    <p class="mt-1 text-stone-600 text-sm">You need an account to post items.</p>

    <?php if ($errors): ?>
        <ul class="mt-5 bg-red-50 border border-red-300 text-red-800 text-sm rounded-md px-4 py-3 list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-4" novalidate>
        <div>
            <label for="name" class="block text-sm font-medium mb-1">Full name</label>
            <input id="name" name="name" type="text" value="<?= e($name) ?>" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email</label>
            <input id="email" name="email" type="email" value="<?= e($email) ?>" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium mb-1">Phone number (optional)</label>
            <input id="phone" name="phone" type="tel" value="<?= e($phone) ?>" class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input id="password" name="password" type="password" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
            </div>
            <div>
                <label for="confirm" class="block text-sm font-medium mb-1">Confirm password</label>
                <input id="confirm" name="confirm" type="password" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
            </div>
        </div>
        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-teal-800 hover:bg-teal-900 text-white font-medium px-4 py-2.5 rounded-md"><?= icon('user', 'w-4 h-4') ?> Create account</button>
    </form>

    <p class="mt-5 text-sm text-stone-600">Already registered? <a href="login.php" class="text-teal-800 font-medium underline">Log in</a></p>
</div>

<?php require 'includes/footer.php'; ?>
