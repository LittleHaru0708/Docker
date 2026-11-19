
<?php
require_once 'db.php';

$results = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $gachaId = 1;

        // ガチャラインナップを取得
        $stmt = $pdo->prepare(
            "SELECT gi.item_id, gi.weight, i.item_name
             FROM gacha_items gi
             INNER JOIN items i ON gi.item_id = i.item_id
             WHERE gi.gacha_id = ?
             ORDER BY gi.item_id"
        );
        $stmt->execute([$gachaId]);
        $items = $stmt->fetchAll();

        if (empty($items)) {
            throw new Exception('ガチャのラインナップが登録されていません。');
        }

        // ウェイト合計
        $totalWeight = array_sum(array_column($items, 'weight'));

        if ($totalWeight <= 0) {
            throw new Exception('抽選ウェイトが不正です。');
        }

        $pdo->beginTransaction();

        // 10連ガチャの履歴グループを作成
        $stmt = $pdo->prepare(
            "INSERT INTO gacha_history_batches (gacha_id)
             VALUES (?)"
        );
        $stmt->execute([$gachaId]);

        $historyId = (int)$pdo->lastInsertId();

        // 履歴明細保存用SQL
        $insert = $pdo->prepare(
            "INSERT INTO gacha_histories
             (history_id, gacha_id, item_id)
             VALUES (?, ?, ?)"
        );

        // 10回抽選
        for ($i = 0; $i < 10; $i++) {

            $hit = random_int(1, $totalWeight);
            $currentTotal = $totalWeight;
            $selectedItem = null;

            foreach ($items as $item) {

                $currentTotal -= (int)$item['weight'];

                if ($currentTotal < $hit) {
                    $selectedItem = $item;
                    break;
                }
            }

            if ($selectedItem === null) {
                throw new Exception('抽選に失敗しました。');
            }

            // DBへ保存
            $insert->execute([
                $historyId,
                $gachaId,
                $selectedItem['item_id']
            ]);

            $results[] = $selectedItem;
        }

        $pdo->commit();

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $results = [];
        $error = 'ガチャ処理に失敗しました。';
    }
}

// ラインナップ表示用
$stmt = $pdo->prepare(
    "SELECT i.item_name, gi.weight
     FROM gacha_items gi
     INNER JOIN items i ON gi.item_id = i.item_id
     WHERE gi.gacha_id = 1
     ORDER BY gi.item_id"
);
$stmt->execute();
$lineup = $stmt->fetchAll();

$total = array_sum(array_column($lineup, 'weight'));
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>10連ガチャ</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px 15px;
    background: #151827;
    color: #fff;
    font-family: sans-serif;
}

.container {
    max-width: 750px;
    margin: auto;
}

h1 {
    text-align: center;
    color: #ffd166;
}

.subtitle {
    text-align: center;
    color: #ccc;
}

.panel {
    background: #252b42;
    border-radius: 15px;
    padding: 20px;
    margin-top: 25px;
}

button {
    display: block;
    width: 100%;
    padding: 18px;
    margin-top: 20px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg, #ff9f43, #ffcc4d);
    color: #222;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    opacity: .85;
}

.items {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.item {
    background: #353d5b;
    border-radius: 10px;
    padding: 18px 10px;
    text-align: center;
}

.item strong {
    display: block;
    margin-bottom: 8px;
}

.rate {
    color: #ffd166;
}

.result {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.result-item {
    padding: 18px 10px;
    background: #353d5b;
    border-radius: 10px;
    text-align: center;
}

.result-item strong {
    display: block;
    margin-bottom: 8px;
}

.rare-1 {
    border: 2px solid #ffdf45;
    color: #ffdf45;
}

.rare-2 {
    border: 2px solid #c084fc;
    color: #d8b4fe;
}

.rare-3 {
    border: 2px solid #60a5fa;
    color: #93c5fd;
}

.rare-4 {
    color: #fff;
}

.rare-5 {
    color: #aaa;
}

.links {
    text-align: center;
    margin-top: 25px;
}

a {
    color: #ffd166;
}

.error {
    color: #ff7777;
    text-align: center;
}
</style>
</head>

<body>
<div class="container">

<h1>★ 10連ガチャ ★</h1>
<p class="subtitle">運命の10回抽選に挑戦しよう！</p>

<div class="panel">
    <h2>ガチャラインナップ</h2>

    <div class="items">
        <?php foreach ($lineup as $item): ?>
            <div class="item">
                <strong>
                    <?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <span class="rate">
                    <?= round($item['weight'] / $total * 100, 2) ?>%
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST">
        <button type="submit">10連ガチャを回す！</button>
    </form>
</div>

<?php if ($error): ?>
    <p class="error">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </p>
<?php endif; ?>

<?php if (!empty($results)): ?>

<div class="panel">
    <h2>★ ガチャ結果 ★</h2>

    <div class="result">
        <?php foreach ($results as $index => $item): ?>

            <?php
            $rarityClass = 'rare-' . $item['item_id'];
            ?>

            <div class="result-item <?= $rarityClass ?>">
                <strong>
                    <?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <?= $index + 1 ?>回目
            </div>

        <?php endforeach; ?>
    </div>

    <p style="text-align:center; margin-top:20px;">
        10連ガチャ完了！
    </p>
</div>

<?php endif; ?>

<div class="links">
    <a href="history.php">ガチャ履歴を見る</a>
</div>

</div>
</body>
</html>
