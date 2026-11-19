
<?php
require_once 'db.php';

$sql = "
    SELECT
        b.history_id,
        b.gacha_id,
        b.created_at,
        h.id,
        h.item_id,
        i.item_name
    FROM gacha_history_batches b
    INNER JOIN gacha_histories h
        ON b.history_id = h.history_id
    INNER JOIN items i
        ON h.item_id = i.item_id
    ORDER BY b.history_id DESC, h.id ASC
";

$stmt = $pdo->query($sql);
$histories = $stmt->fetchAll();

$grouped = [];

foreach ($histories as $row) {
    $grouped[$row['history_id']]['created_at'] = $row['created_at'];
    $grouped[$row['history_id']]['items'][] = $row;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ガチャ履歴</title>

<style>
body {
    background: #151827;
    color: white;
    font-family: sans-serif;
    padding: 25px 15px;
}

.container {
    max-width: 750px;
    margin: auto;
}

h1 {
    color: #ffd166;
    text-align: center;
}

.history {
    background: #252b42;
    padding: 20px;
    border-radius: 12px;
    margin: 20px 0;
}

.item {
    background: #353d5b;
    padding: 12px;
    margin: 7px 0;
    border-radius: 7px;
}

a {
    color: #ffd166;
}
</style>
</head>

<body>
<div class="container">

<h1>ガチャ履歴</h1>

<?php if (empty($grouped)): ?>

    <p>まだガチャ履歴がありません。</p>

<?php else: ?>

    <?php foreach ($grouped as $historyId => $group): ?>

        <div class="history">

            <h2>履歴ID：<?= (int)$historyId ?></h2>

            <p>
                実行日時：
                <?= htmlspecialchars($group['created_at'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <?php foreach ($group['items'] as $index => $item): ?>

                <div class="item">
                    <?= $index + 1 ?>回目：
                    <?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?>
                </div>

            <?php endforeach; ?>

            <p>
                合計 <?= count($group['items']) ?> 個
            </p>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

<p><a href="index.php">ガチャ画面に戻る</a></p>

</div>
</body>
</html>
