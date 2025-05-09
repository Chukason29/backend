<?php
    
    #TODO ==> check if the token is valid and not expired
    $email = getEmailFromToken($verify_token);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(["status" => "error", 'message' => 'Invalid link or link expired'], 400);
        exit;
    }

    try {
        $pdo->beginTransaction();
        $updateToken = $pdo->prepare("UPDATE link_token SET is_used = :is_used WHERE email = :email");
        $updateToken->bindValue(':is_used', TRUE, PDO::PARAM_BOOL);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $updateToken->execute();

        // Update is_active in users table
        $updateUser = $pdo->prepare("UPDATE users SET is_active = :is_active WHERE email = :email");
        $updateUser->bindValue(':is_active', TRUE, PDO::PARAM_BOOL);
        $updateUser->bindValue(':email', $email, PDO::PARAM_STR);
        $updateUser->execute();
        //
        
    // Commit transaction
        if ($pdo->commit()) {
            header("Location: " . rtrim($config['url']['BASE_URL'], '/') . "/verification-success.html");
            exit;
        }else{
            header("Location: " . rtrim($config['url']['BASE_URL'], '/') . "/verification-failure.html");
            exit;
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        respond(["status" => "error", 'message' => 'Database error: ' . $e->getMessage()], 500);
    } catch (Exception $e) {
        $pdo->rollBack();
        respond(["status" => "error", 'message' => 'Error: ' . $e->getMessage()], 500);
    } catch (\Throwable $th) {
        $pdo->rollBack();
        respond(["status" => "error", 'message' => 'Error: ' . $e->getMessage()], 500);
    }
    
