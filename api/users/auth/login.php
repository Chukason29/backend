<?php
    session_start();
    #TODO
    // JSON body
    $data = json_decode(file_get_contents('php://input'), true);

    // Respond helper
    function respond($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    #TODO ==> Make sure email and password is inputted
    if (!isset($data['email'], $data['password'])) {
        respond(["status" => "error",'message' => 'All fields are required'], 400);
        exit;
    }

    $email = sanitizeInput($data['email']);
    $password = $data['password'];


    #TODO ==> Check if account exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    

    #TODO ==> Check if account exists
    if (!$user){
        respond(["status" => "error", 'message' => 'Account not found'], 400);
        exit;
    }
    $_SESSION['name'] = $name = $user['name'];
    $_SESSION['user_id'] = $user_id = $user['id'];
    $_SESSION['email'] = $user_id = $user['email'];
    $_SESSION['organization_id'] = $organization_id = $user['organization_id'];
    $role_id = $user['role_id'];
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :role_id");
    $stmt->bindValue(':role_id', $role_id);
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $_SESSION['role_name'] = $role_name = $role['role_name'];
    
    #TODO ==> Check if account is activated
    if (!$user['is_active']){ 
        $token = generateTimedToken($email, 172800); //expires in 48hours after creation
        $verifyLink = $config['url']['BASE_URL'].'/api/verify?token='.$token;

        try {
                #TODO ==> Updating the token table for the user
                $pdo->beginTransaction();
                $updateToken = $pdo->prepare("DELETE FROM link_token  WHERE email = :email");
                $updateToken->bindValue(':email', $email, PDO::PARAM_STR);
                $updateToken->execute();

                
                $updateUser = $pdo->prepare("INSERT INTO link_token (email, token) VALUES (:email, :token)");
                $updateUser->bindValue(':token', $token, PDO::PARAM_STR);
                $updateUser->bindValue(':email', $email, PDO::PARAM_STR);
                $updateUser->execute();
            //
            
        // Commit transaction
            if ($pdo->commit() && sendHTMLEmail($email, $name, $verifyLink, dirname(__DIR__, 2)."/templates/email_verification.html")) {
                respond(["status" => "error", 'message' => 'Account not activated, activation link sent to your mail'], 400);
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
    }

    if ($organization_id == null){ 
        $_SESSION['user_id'] = $user_id;
        header("Location: " . rtrim($config['url']['BASE_URL'], '/') . "/create-organization");
        exit;
    }

    if (password_verify($password, $user['password_hash'])) {
        // Return session or token info
        require_once __DIR__ . '/authenticate.php';
    }