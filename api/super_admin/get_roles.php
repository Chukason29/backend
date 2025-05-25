<?php
// Respond helper

try {
    $stmt = $pdo->prepare("SELECT * FROM roles");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respond($roles, 200);

} catch (PDOException $e) {
    $pdo->rollBack();
    respond(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    $pdo->rollBack();
    respond(['error' => 'Error: ' . $e->getMessage()], 500);
}

