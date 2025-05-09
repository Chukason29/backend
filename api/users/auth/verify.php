<?php
    
    #TODO ==> check if the token is valid and not expired
    $email = getEmailFromToken($verify_token);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(["status" => "error", 'message' => 'Invalid link or link expired'], 400);
        exit;
    }

    try {
        $updateToken = $pdo->prepare("UPDATE link_token SET is_used = :is_used WHERE email = :email");
    $updateToken->bindValue(':is_used', TRUE, PDO::PARAM_BOOL);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $updateToken->execute();

    // Update is_active in users table
    $updateUser = $pdo->prepare("UPDATE users SET is_active = :is_active WHERE email = :email");
    $updateToken->bindValue(':is_active', TRUE, PDO::PARAM_BOOL);
    $updateUser->bindValue(':email', $email, PDO::PARAM_STR);
    $updateUser->execute();
    //
    
// Commit transaction
    if ($pdo->commit()) {
        header("Location:". $config['url']['BASE_URL']."/verification-success.html");
    }else{
        header("Location:". $config['url']['BASE_URL']."/verification-failed.html");
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
    
        
    #TODO ==> If the token is valid and not expired, update the token table with true as the check column

    #TODO ==> If the token is valid and not expired, update the user table with true as the check column
    respond(["token" => $verify_token], 200);
    exit;
