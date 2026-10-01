<?php
$links = is_logged_in()
    ? [['index.php', 'Browse items', 'search'], ['post.php', 'Post an item', 'plus'], ['my-items.php', 'My items', 'list'], ['logout.php', 'Log out', 'log-out']]
    : [['index.php', 'Browse items', 'search'], ['login.php', 'Log in', 'log-in'], ['register.php', 'Create account', 'user']];
$current = basename($_SERVER['PHP_SELF']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Re-Use') ?> | Re-Use</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-100 text-stone-800 min-h-screen flex flex-col antialiased">

<header class="bg-white border-b border-stone-300 sticky top-0 z-30">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-2 text-teal-800 font-bold text-xl">
            <?= icon('package', 'w-7 h-7') ?> Re-Use
        </a>

        <nav class="hidden md:flex items-center gap-1" aria-label="Main">
            <?php foreach ($links as $l): ?>
                <a href="<?= $l[0] ?>" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium <?= $current === $l[0] ? 'bg-teal-800 text-white' : 'text-stone-700 hover:bg-stone-100' ?>">
                    <?= icon($l[2], 'w-4 h-4') ?> <?= $l[1] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <button id="menuBtn" type="button" class="md:hidden p-2 rounded-md text-stone-700 hover:bg-stone-100" aria-label="Open menu" aria-expanded="false">
            <span id="iconOpen"><?= icon('menu', 'w-6 h-6') ?></span>
            <span id="iconClose" class="hidden"><?= icon('x', 'w-6 h-6') ?></span>
        </button>
    </div>

    <nav id="mobileMenu" class="hidden md:hidden border-t border-stone-200 bg-white px-4 py-2" aria-label="Mobile">
        <?php foreach ($links as $l): ?>
            <a href="<?= $l[0] ?>" class="flex items-center gap-3 px-3 py-3 rounded-md text-sm font-medium <?= $current === $l[0] ? 'bg-teal-800 text-white' : 'text-stone-700 hover:bg-stone-100' ?>">
                <?= icon($l[2], 'w-5 h-5') ?> <?= $l[1] ?>
            </a>
        <?php endforeach; ?>
    </nav>
</header>

<main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
<?php if ($flash): ?>
    <div class="mb-6 flex items-start gap-3 rounded-md border px-4 py-3 text-sm <?= $flash['type'] === 'success' ? 'bg-teal-50 border-teal-300 text-teal-900' : 'bg-red-50 border-red-300 text-red-800' ?>" role="alert">
        <?= icon($flash['type'] === 'success' ? 'check' : 'x', 'w-5 h-5 mt-0.5 shrink-0') ?>
        <span><?= e($flash['message']) ?></span>
    </div>
<?php endif; ?>
