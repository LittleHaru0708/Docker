
CREATE DATABASE IF NOT EXISTS gacha
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE gacha;

-- アイテムマスタ
CREATE TABLE IF NOT EXISTS items (
    item_id INT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL
);

-- ガチャアイテムマスタ
CREATE TABLE IF NOT EXISTS gacha_items (
    gacha_id INT NOT NULL,
    item_id INT NOT NULL,
    weight INT NOT NULL,
    PRIMARY KEY (gacha_id, item_id),
    FOREIGN KEY (item_id) REFERENCES items(item_id),
    CHECK (weight > 0)
);

-- ガチャ実行履歴（10連単位）
CREATE TABLE IF NOT EXISTS gacha_history_batches (
    history_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    gacha_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ガチャ履歴明細
CREATE TABLE IF NOT EXISTS gacha_histories (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    history_id BIGINT NOT NULL,
    gacha_id INT NOT NULL,
    item_id INT NOT NULL,
    FOREIGN KEY (history_id)
        REFERENCES gacha_history_batches(history_id),
    FOREIGN KEY (item_id)
        REFERENCES items(item_id)
);

-- アイテム初期データ
INSERT INTO items (item_id, item_name) VALUES
(1, '神レアアイテム'),
(2, '超レアアイテム'),
(3, 'レアアイテム'),
(4, '普通のアイテム'),
(5, 'ガラクタ');

-- ガチャラインナップ
INSERT INTO gacha_items (gacha_id, item_id, weight) VALUES
(1, 1, 10),
(1, 2, 90),
(1, 3, 200),
(1, 4, 300),
(1, 5, 400);
