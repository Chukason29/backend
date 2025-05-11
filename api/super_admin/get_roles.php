<?php
// Respond helper
function respond($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

try {
    $stmt1 = $pdo->prepare("SELECT * FROM roles");
    $stmt1->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    respond($user);
} catch (PDOException $e) {
    $pdo->rollBack();
    respond(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    $pdo->rollBack();
    respond(['error' => 'Error: ' . $e->getMessage()], 500);
}

