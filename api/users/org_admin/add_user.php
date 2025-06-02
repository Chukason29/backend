<?php
## tokenization.php is used for
#checking if access token is valid or expired
#decrypting the user id from the token
# checking if user has access to the requested resource
require __DIR__ . '/../../tokenization.php'; 

respond(['status' => 'sucess', 'message' => "You can now add user"], 200);
exit;

    