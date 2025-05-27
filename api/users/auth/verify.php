<?php

    $data = json_decode(file_get_contents('php://input'), true);

    
    #TODO ==> check if the token is valid and not expired
    $email = getEmailFromToken($verify_token);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(["status" => "error", 'message' => 'Invalid link or link expired'], 400);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM link_token WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        /*if (!$result["token"] || $result["token"] !== $verify_token) {
             //respond(["status" => "error", "message"=> "invalid token","redirect_url" => $config['url']['BASE_URL'] . "/auth/verify/?token=" . $verify_token], 200);
             header("Location: " . $config['url']['BASE_URL'] . "/auth/verify/?status=error&message=invalid token?token=" . $verify_token);
            exit;
        }*/
        if ($result["is_used"] === TRUE) {
             //respond(["status" => "error", "message"=> "invalid token","redirect_url" => $config['url']['BASE_URL'] . "/auth/verify/?token=" . $verify_token], 200);
            header("Location: " . $config['url']['BASE_URL'] . "/auth/verify/?status=error&message=link already used&token=" . $verify_token);
             exit;
        }
    } catch (PDOException $e) {
        respond(["status" => "error", 'message' => 'Database error: ' . $e->getMessage()], 500);
        exit;
    } catch (Exception $e) {
        respond(["status" => "error", 'message' => 'Error: ' . $e->getMessage()], 500);
        exit;
    } catch (\Throwable $th) {
        respond(["status" => "error", 'message' => 'Error: ' . $th->getMessage()], 500);
        exit;
    }

    try {
        $pdo->beginTransaction();
        $updateToken = $pdo->prepare("UPDATE link_token SET is_used = :is_used WHERE email = :email");
        $updateToken->bindValue(':is_used', TRUE, PDO::PARAM_BOOL);
        $updateToken->bindValue(':email', $email, PDO::PARAM_STR);
        $updateToken->execute();

        // Update is_active in users table
        $updateUser = $pdo->prepare("UPDATE users SET is_active = :is_active WHERE email = :email");
        $updateUser->bindValue(':is_active', TRUE, PDO::PARAM_BOOL);
        $updateUser->bindValue(':email', $email, PDO::PARAM_STR);
        $updateUser->execute();
        //
        
    // Commit transaction
        if ($pdo->commit()) {
            // Redirect to the verification success page
            
            
            header("Location: " . $config['url']['BASE_URL'] . "/auth/verify/?status=success&message=verification successful&token=" . $verify_token);#respond(["status" => "success", "message"=> "verification successful","redirect_url" => $config['url']['BASE_URL'] . "/auth/verify/?token=" . $verify_token], 200);
            exit;
        }else{
            // Redirect to the verification success page
            header("Location: " . $config['url']['BASE_URL'] . "/auth/verify/?status=error&message=verification failed&token=" . $verify_token);
            exit;
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        respond(["status" => "error", 'message' => 'Database error: ' . $e->getMessage()], 500);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        respond(["status" => "error", 'message' => 'Error: ' . $e->getMessage()], 500);
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        respond(["status" => "error", 'message' => 'Error: ' . $e->getMessage()], 500);
    }
    
