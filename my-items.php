<?php
require 'config.php';
require_login();
$pageTitle = 'My items';
$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = $conn->prepare('SELECT image FROM items WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
        if ($action === 'claimed' || $action === 'available') {
            $stmt = $conn->prepare('UPDATE items SET status = ? WHERE id = ? AND user_id = ?');
            $stmt->bind_param('sii', $action, $id, $uid);
            $stmt->execute();
            set_flash('success', $action === 'claimed' ? 'Item marked as claimed.' : 'Item is available again.');
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare('DELETE FROM items WHERE id = ? AND user_id = ?');
            $stmt->bind_param('ii', $id, $uid);
            $stmt->execute();
            $file = UPLOAD_DIR . basename($row['image']);
            if (is_file($file)) unlink($file);
            set_flash('success', 'Item deleted.');
        }
    }
    header('Location: my-items.php');
    exit;
}

$stmt = $conn->prepare('SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $uid);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require 'includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold">My items</h1>
        <p class="text-sm text-stone-600">Mark an item as claimed once someone picks it up.</p>
    </div>
    <a href="post.php" class="inline-flex items-center justify-center gap-2 bg-teal-800 hover:bg-teal-900 text-white font-medium px-4 py-2.5 rounded-md"><?= icon('plus', 'w-4 h-4') ?> Post an item</a>
</div>

<?php if (!$items): ?>
    <div class="bg-white border border-stone-300 rounded-md py-14 px-6 text-center">
        <div class="mx-auto w-12 h-12 rounded-md bg-stone-100 text-stone-500 flex items-center justify-center"><?= icon('gift', 'w-6 h-6') ?></div>
        <h2 class="mt-4 text-lg font-semibold">You have not posted anything yet</h2>
        <p class="mt-1 text-stone-600">Post equipment you no longer use so someone else can benefit.</p>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($items as $it): ?>
            <div class="bg-white border border-stone-300 rounded-md p-4 flex flex-col sm:flex-row gap-4">
                <img src="<?= UPLOAD_URL . e($it['image']) ?>" alt="<?= e($it['title']) ?>" class="w-full sm:w-32 h-40 sm:h-24 object-cover rounded-md bg-stone-200">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="item.php?id=<?= (int)$it['id'] ?>" class="font-semibold text-lg hover:underline"><?= e($it['title']) ?></a>
                        <span class="text-xs font-medium px-2 py-0.5 rounded border <?= $it['status'] === 'claimed' ? 'bg-stone-200 text-stone-700 border-stone-300' : 'bg-teal-50 text-teal-900 border-teal-200' ?>">
                            <?= $it['status'] === 'claimed' ? 'Claimed' : 'Available' ?>
                        </span>
                    </div>
                    <p class="text-sm text-stone-600 mt-1"><?= e($it['category']) ?>, <?= e($it['item_condition']) ?>. Posted <?= date('M j, Y', strtotime($it['created_at'])) ?></p>
                </div>
                <div class="flex sm:flex-col gap-2 sm:items-stretch">
                    <form method="post" class="flex-1">
                        <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                        <input type="hidden" name="action" value="<?= $it['status'] === 'claimed' ? 'available' : 'claimed' ?>">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 border border-stone-300 hover:bg-stone-100 text-sm font-medium px-3 py-2 rounded-md">
                            <?= $it['status'] === 'claimed' ? icon('undo', 'w-4 h-4') . ' Relist' : icon('check', 'w-4 h-4') . ' Mark claimed' ?>
                        </button>
                    </form>
                    <form method="post" class="flex-1" onsubmit="return confirm('Delete this item permanently?');">
                        <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 border border-red-300 text-red-700 hover:bg-red-50 text-sm font-medium px-3 py-2 rounded-md"><?= icon('trash', 'w-4 h-4') ?> Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
