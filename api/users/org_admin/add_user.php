<?php

require_once dirname(__DIR__, 2) . '/tokenization.php';


respond(['status' => 'error', 'message' => $decoded_token], 200);

