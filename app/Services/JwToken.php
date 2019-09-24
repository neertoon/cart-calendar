<?php


namespace App\Services;


class JwToken {
    public static function getData($token) {
        $jwt = explode('.', $token);

// Extract the middle part, base64 decode, then json_decode it
        $base = base64_decode($jwt[1]);
        return json_decode($base, true);
    }
}
