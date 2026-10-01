<?php
require 'config.php';
require_login();
$pageTitle = 'Post an item';

$errors = [];
$f = ['title' => '', 'category' => '', 'item_condition' => '', 'location' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $_) {
        $f[$k] = trim($_POST[$k] ?? '');
    }

    if ($f['title'] === '' || strlen($f['title']) > 120) $errors[] = 'Enter a title (up to 120 characters).';
    if (!in_array($f['category'], $categories, true)) $errors[] = 'Choose a category.';
    if (!in_array($f['item_condition'], $conditions, true)) $errors[] = 'Choose the item condition.';
    if ($f['location'] === '') $errors[] = 'Enter the pickup location.';
    if ($f['description'] === '') $errors[] = 'Enter a description.';

    $ext = '';
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Upload a photo of the item.';
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'The image could not be uploaded. Try a smaller file.';
    } else {
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be 2 MB or smaller.';
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            $errors[] = 'Image must be a JPG, PNG or WEBP file.';
        } else {
            $ext = $allowed[$mime];
        }
    }

    if (!$errors) {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
        $filename = bin2hex(random_bytes(8)) . '.' . $ext;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $filename)) {
            $errors[] = 'Could not save the image. Check the uploads folder permissions.';
        } else {
            $stmt = $conn->prepare('INSERT INTO items (user_id, title, description, category, item_condition, location, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('issssss', $_SESSION['user_id'], $f['title'], $f['description'], $f['category'], $f['item_condition'], $f['location'], $filename);
            $stmt->execute();
            set_flash('success', 'Your item has been posted.');
            header('Location: item.php?id=' . $conn->insert_id);
            exit;
        }
    }
}

require 'includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white border border-stone-300 rounded-md p-6 sm:p-8">
    <h1 class="text-2xl font-bold">Post an item</h1>
    <p class="mt-1 text-stone-600 text-sm">Describe what you are donating. Claimers will see your email and phone number when they press the contact button.</p>

    <?php if ($errors): ?>
        <ul class="mt-5 bg-red-50 border border-red-300 text-red-800 text-sm rounded-md px-4 py-3 list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mt-6 space-y-5">
        <div>
            <label for="title" class="block text-sm font-medium mb-1">Item name</label>
            <input id="title" name="title" type="text" maxlength="120" value="<?= e($f['title']) ?>" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="category" class="block text-sm font-medium mb-1">Category</label>
                <select id="category" name="category" required class="w-full border border-stone-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-teal-700">
                    <option value="">Choose a category</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c) ?>" <?= $f['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="item_condition" class="block text-sm font-medium mb-1">Condition</label>
                <select id="item_condition" name="item_condition" required class="w-full border border-stone-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-teal-700">
                    <option value="">Choose condition</option>
                    <?php foreach ($conditions as $c): ?>
                        <option value="<?= e($c) ?>" <?= $f['item_condition'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label for="location" class="block text-sm font-medium mb-1">Pickup location</label>
            <input id="location" name="location" type="text" value="<?= e($f['location']) ?>" placeholder="Barangay or city" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium mb-1">Description</label>
            <textarea id="description" name="description" rows="5" required class="w-full border border-stone-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-700"><?= e($f['description']) ?></textarea>
        </div>

        <div>
            <label for="image" class="block text-sm font-medium mb-1">Photo</label>
            <label for="image" class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-stone-300 rounded-md py-8 px-4 text-center cursor-pointer hover:border-teal-700">
                <img id="preview" class="hidden max-h-48 rounded-md" alt="Selected photo preview">
                <span id="uploadHint" class="flex flex-col items-center gap-2 text-stone-600">
                    <?= icon('upload', 'w-7 h-7') ?>
                    <span class="text-sm">Choose a JPG, PNG or WEBP image (max 2 MB)</span>
                </span>
            </label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required class="sr-only">
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
            <a href="index.php" class="inline-flex items-center justify-center gap-2 border border-stone-300 hover:bg-stone-100 px-4 py-2.5 rounded-md font-medium">Cancel</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 bg-teal-800 hover:bg-teal-900 text-white font-medium px-5 py-2.5 rounded-md"><?= icon('plus', 'w-4 h-4') ?> Post item</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('image').addEventListener('change', function () {
        const file = this.files[0];
        const img = document.getElementById('preview');
        const hint = document.getElementById('uploadHint');
        if (file) {
            img.src = URL.createObjectURL(file);
            img.classList.remove('hidden');
            hint.classList.add('hidden');
        }
    });
</script>

<?php require 'includes/footer.php'; ?>
