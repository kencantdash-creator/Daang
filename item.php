<?php
require 'config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT i.*, u.name AS donor, u.email AS donor_email, u.phone AS donor_phone FROM items i JOIN users u ON u.id = i.user_id WHERE i.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$it = $stmt->get_result()->fetch_assoc();

if (!$it) {
    http_response_code(404);
    $pageTitle = 'Item not found';
    require 'includes/header.php';
    echo '<div class="bg-white border border-stone-300 rounded-md py-14 px-6 text-center"><h1 class="text-xl font-semibold">Item not found</h1><p class="mt-1 text-stone-600">It may have been removed by the donor.</p><a href="index.php" class="mt-5 inline-flex items-center gap-2 bg-teal-800 text-white px-4 py-2.5 rounded-md font-medium">' . icon('arrow-left', 'w-4 h-4') . ' Back to items</a></div>';
    require 'includes/footer.php';
    exit;
}

$pageTitle = $it['title'];
$isOwner = is_logged_in() && $_SESSION['user_id'] == $it['user_id'];
require 'includes/header.php';
?>

<a href="index.php" class="inline-flex items-center gap-2 text-sm text-teal-800 font-medium mb-5 hover:underline"><?= icon('arrow-left', 'w-4 h-4') ?> Back to items</a>

<div class="bg-white border border-stone-300 rounded-md overflow-hidden grid md:grid-cols-2">
    <div class="bg-stone-200 md:min-h-[420px]">
        <img src="<?= UPLOAD_URL . e($it['image']) ?>" alt="<?= e($it['title']) ?>" class="w-full h-full object-cover max-h-[520px]">
    </div>

    <div class="p-6 sm:p-8 flex flex-col">
        <div class="flex flex-wrap gap-2 text-xs font-medium">
            <span class="bg-teal-50 text-teal-900 border border-teal-200 px-2 py-0.5 rounded"><?= e($it['category']) ?></span>
            <span class="bg-amber-50 text-amber-900 border border-amber-200 px-2 py-0.5 rounded"><?= e($it['item_condition']) ?></span>
            <?php if ($it['status'] === 'claimed'): ?>
                <span class="bg-stone-200 text-stone-700 border border-stone-300 px-2 py-0.5 rounded">Already claimed</span>
            <?php endif; ?>
        </div>

        <h1 class="mt-3 text-2xl sm:text-3xl font-bold leading-tight"><?= e($it['title']) ?></h1>

        <div class="mt-3 space-y-1.5 text-sm text-stone-600">
            <p class="flex items-center gap-2"><?= icon('map-pin', 'w-4 h-4') ?> <?= e($it['location']) ?></p>
            <p class="flex items-center gap-2"><?= icon('user', 'w-4 h-4') ?> Donated by <?= e($it['donor']) ?> on <?= date('M j, Y', strtotime($it['created_at'])) ?></p>
        </div>

        <p class="mt-5 text-stone-700 whitespace-pre-line"><?= e($it['description']) ?></p>

        <div class="mt-auto pt-6">
            <?php if ($isOwner): ?>
                <a href="my-items.php" class="inline-flex items-center gap-2 border border-stone-300 hover:bg-stone-100 px-4 py-2.5 rounded-md font-medium"><?= icon('list', 'w-4 h-4') ?> Manage my items</a>
            <?php elseif ($it['status'] === 'claimed'): ?>
                <p class="text-sm text-stone-600">This item has already been claimed.</p>
            <?php else: ?>
                <button id="revealBtn" type="button" class="w-full inline-flex items-center justify-center gap-2 bg-amber-400 hover:bg-amber-300 text-stone-900 font-semibold px-4 py-3 rounded-md">
                    <?= icon('eye', 'w-5 h-5') ?> Show contact details
                </button>

                <div id="contactBox" class="hidden border border-teal-300 bg-teal-50 rounded-md p-4 space-y-3">
                    <p class="text-sm text-teal-900 font-medium">Contact <?= e($it['donor']) ?> to arrange pickup.</p>
                    <a href="mailto:<?= e($it['donor_email']) ?>" class="flex items-center gap-3 text-teal-900 hover:underline break-all"><?= icon('mail', 'w-5 h-5 shrink-0') ?> <?= e($it['donor_email']) ?></a>
                    <?php if ($it['donor_phone']): ?>
                        <a href="tel:<?= e($it['donor_phone']) ?>" class="flex items-center gap-3 text-teal-900 hover:underline"><?= icon('phone', 'w-5 h-5 shrink-0') ?> <?= e($it['donor_phone']) ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const rb = document.getElementById('revealBtn');
    if (rb) {
        rb.addEventListener('click', () => {
            document.getElementById('contactBox').classList.remove('hidden');
            rb.classList.add('hidden');
        });
    }
</script>

<?php require 'includes/footer.php'; ?>
