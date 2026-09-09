<?php

function ensurePiRenderTokensTable(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS pi_render_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        token_hash CHAR(64) NOT NULL UNIQUE,
        user_id INT UNSIGNED NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pi_render_expiry (expires_at),
        INDEX idx_pi_render_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function issuePiRenderToken(PDO $db, int $userId): string
{
    ensurePiRenderTokensTable($db);
    $db->exec("DELETE FROM pi_render_tokens WHERE expires_at < NOW() OR used_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $token = bin2hex(random_bytes(32));
    $stmt = $db->prepare("INSERT INTO pi_render_tokens (token_hash, user_id, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 MINUTE))");
    $stmt->execute([hash('sha256', $token), $userId]);
    return $token;
}

function consumePiRenderToken(PDO $db, string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    ensurePiRenderTokensTable($db);
    $hash = hash('sha256', $token);

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT user_id FROM pi_render_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at >= NOW() FOR UPDATE");
        $stmt->execute([$hash]);
        $userId = (int)($stmt->fetchColumn() ?: 0);
        if (!$userId) {
            $db->rollBack();
            return null;
        }

        $db->prepare("UPDATE pi_render_tokens SET used_at = NOW() WHERE token_hash = ?")->execute([$hash]);
        $userStmt = $db->prepare("SELECT id, name, email, role, team FROM users WHERE id = ? AND COALESCE(is_active, 1) = 1 LIMIT 1");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();
        $db->commit();
        return $user ?: null;
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

