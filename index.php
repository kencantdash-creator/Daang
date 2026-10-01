<?php
require 'config.php';
$pageTitle = 'Browse items';

$q = trim($_GET['q'] ?? '');
$cat = $_GET['category'] ?? '';

$sql = "SELECT i.*, u.name AS donor FROM items i JOIN users u ON u.id = i.user_id WHERE i.status = 'available'";
$types = '';
$params = [];

if ($q !== '') {
    $sql .= " AND (i.title LIKE ? OR i.description LIKE ? OR i.location LIKE ?)";
    $like = "%$q%";
    $types .= 'sss';
    array_push($params, $like, $like, $like);
}
if ($cat !== '' && in_array($cat, $categories, true)) {
    $sql .= " AND i.category = ?";
    $types .= 's';
    $params[] = $cat;
}
$sql .= " ORDER BY i.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require 'includes/header.php';
?>

<section class="bg-teal-800 text-white rounded-md px-6 py-8 mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Give used equipment a second life</h1>
    <p class="mt-2 text-teal-50 max-w-2xl">Browse items donated by people in your community. Found something you need? Open it and contact the donor to claim it.</p>
    <?php if (!is_logged_in()): ?>
        <div class="mt-5 flex flex-col sm:flex-row gap-3">
            <a href="register.php" class="inline-flex items-center justify-center gap-2 bg-amber-400 hover:bg-amber-300 text-stone-900 font-semibold px-4 py-2.5 rounded-md"><?= icon('user', 'w-4 h-4') ?> Create account</a>
            <a href="login.php" class="inline-flex items-center justify-center gap-2 border border-white/60 hover:bg-teal-700 text-white font-medium px-4 py-2.5 rounded-md"><?= icon('log-in', 'w-4 h-4') ?> Log in</a>
        </div>
    <?php else: ?>
        <a href="post.php" class="mt-5 inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-stone-900 font-semibold px-4 py-2.5 rounded-md"><?= icon('plus', 'w-4 h-4') ?> Post an item</a>
    <?php endif; ?>
</section>

<form method="get" class="bg-white border border-stone-300 rounded-md p-4 mb-8 grid gap-3 sm:grid-cols-[1fr_220px_auto]">
    <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-500"><?= icon('search', 'w-4 h-4') ?></span>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title, description or location" class="w-full border border-stone-300 rounded-md pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
    </div>
    <select name="category" class="border border-stone-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-teal-700">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= e($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="inline-flex items-center justify-center gap-2 bg-teal-800 hover:bg-teal-900 text-white font-medium px-5 py-2 rounded-md"><?= icon('search', 'w-4 h-4') ?> Search</button>
</form>

<?php if (!$items): ?>
    <div class="bg-white border border-stone-300 rounded-md py-14 px-6 text-center">
        <div class="mx-auto w-12 h-12 rounded-md bg-stone-100 text-stone-500 flex items-center justify-center"><?= icon('package', 'w-6 h-6') ?></div>
        <h2 class="mt-4 text-lg font-semibold">No items found</h2>
        <p class="mt-1 text-stone-600">Try a different search, or be the first to post something.</p>
    </div>
<?php else: ?>
    <p class="text-sm text-stone-600 mb-4"><?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?> available</p>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($items as $it): ?>
            <a href="item.php?id=<?= (int)$it['id'] ?>" class="bg-white border border-stone-300 rounded-md overflow-hidden flex flex-col hover:border-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-700">
                <div class="aspect-[4/3] bg-stone-200">
                    <img src="<?= UPLOAD_URL . e($it['image']) ?>" alt="<?= e($it['title']) ?>" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <div class="flex flex-wrap gap-2 text-xs font-medium">
                        <span class="bg-teal-50 text-teal-900 border border-teal-200 px-2 py-0.5 rounded"><?= e($it['category']) ?></span>
                        <span class="bg-amber-50 text-amber-900 border border-amber-200 px-2 py-0.5 rounded"><?= e($it['item_condition']) ?></span>
                    </div>
                    <h2 class="mt-3 font-semibold text-lg leading-snug"><?= e($it['title']) ?></h2>
                    <p class="mt-1 text-sm text-stone-600 line-clamp-2"><?= e($it['description']) ?></p>
                    <div class="mt-auto pt-4 flex items-center justify-between text-sm text-stone-600">
                        <span class="flex items-center gap-1.5"><?= icon('map-pin', 'w-4 h-4') ?> <?= e($it['location']) ?></span>
                        <span><?= date('M j', strtotime($it['created_at'])) ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
