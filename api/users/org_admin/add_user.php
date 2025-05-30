<?php

require __DIR__ . '/../../tokenization.php';


respond(['status' => 'error', 'message' => $decoded_token], 200);

