<?php
define('HCAPTCHA_SECRET', 'your_secret_key');

function verifyHcaptcha(string $token, string $ip): bool {
    $payload = http_build_query([
        "secret"   => HCAPTCHA_SECRET,
        "response" => $token,
        "remoteip" => $ip,
        "sitekey"  => "611ee0d8-1f5e-4436-99a6-51720f0827fe",
    ]);
    $ctx = stream_context_create([
        "http" => [
            "method"  => "POST",
            "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
            "content" => $payload,
            "timeout" => 5,
        ],
    ]);
    $raw = file_get_contents("https://api.hcaptcha.com/siteverify", false, $ctx);
    $j = json_decode($raw, true);
    return !empty($j["success"]);
}
?>
