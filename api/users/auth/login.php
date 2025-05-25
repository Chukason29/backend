<?php
    session_start();

    $data = json_decode(file_get_contents('php://input'), true);
   
    #TODO ==> Make sure email and password is inputted
    if (!isset($data['email'], $data['password'])) {
        respond(["status" => "error",'message' => 'All fields are required'], 400);
        exit;
    }
    
    $email = sanitizeInput($data['email']);
    $password = $data['password'];
    $token = generateTimedToken($email, 172800); //expires in 48hours after creation


    #TODO ==> Check if account exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    #TODO ==> Check if account exists
    if (!$user){
        respond(["status" => "error", 'message' => 'Incorrect email or password'], 400);
        exit;
    }
    #TODO ==> Check if user is deleted
    if ($user['is_deleted'] == TRUE) {
        # code...
        respond(["status" => "error", 'message' => 'Account has been deactivated'], 400);
        exit;
    }
    #TODO ==> Check if password is correct
    if (!password_verify($password, $user['password_hash'])) {
        respond(["status" => "error", 'message' => 'Incorrect email or password'], 400);
        exit;
    }
    
    $name = $user['name'];
    $user_id = $user['id'];
    $email = $user['email'];
    $organization_id = $user['organization_id'];
    $role_id = $user['role_id'];


    #Collecting the role name from the roles table for the user
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :role_id");
    $stmt->bindValue(':role_id', $role_id);
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$role) {
        respond(["status" => "error", 'message' => 'Role not found'], 500);
        exit;
    }
    
    $role_name = $role['role_name'];

    
    #TODO ==> Check if account is activated
    if (!$user['is_active'] && $role_name == $config['roles']['ORGANIZATION_ADMIN']){ 
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
            if ($pdo->commit() && sendHTMLEmail($email, $name, $verifyLink, dirname(__DIR__, 2)."/templates/email_verification.html", $config['mail']['password'])) {
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
    }elseif (!$user['is_active'] && $role_name != $config['roles']['ORGANIZATION_ADMIN']) {
        #TODO ==> If the user is not an organization admin, redirect to the role password reset page
        response([
            "status" => "redirect", 
            "redirect_url" => $config['url']['BASE_URL'] . '/auth/role-password-reset?token='.$token
        ]);
        exit;
    }
    
    

    //Adding them is session variables
    $_SESSION['name'] = $name;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['organization_id'] = $organization_id ?? null;
    $_SESSION['role_name'] = $role_name;

    
    

    #TODO ==> Check if the user is organization admin and organization_id is null
    if ($role_name == $config['roles']['ORGANIZATION_ADMIN'] && $user['organization_id'] == null){ 
            respond([
                "status" => "redirect",
                "redirect_url" => $config['url']['BASE_URL'] . '/auth/organization'
            ]);
            exit;
    }
    
    //require_once __DIR__ . '/authenticate.php';
    