<?php


namespace App\Services;


class CalendarClient {
    public function get($code = '') {

        $client = new \Google_Client();
        $client->setApplicationName($_ENV['CALENDAR_APP_NAME']);
        $client->setScopes([\Google_Service_Calendar::CALENDAR, 'https://www.googleapis.com/auth/userinfo.profile', 'email']);
        $client->setAuthConfig($this->getConfig());
        $client->setAccessType('online');
        if (!empty($code)) {
            $cred = $client->fetchAccessTokenWithAuthCode($code);
            $_SESSION['token'] = $cred;
        } else {
            $client->setAccessToken($_SESSION['token']);
        }

        file_put_contents('server_token.txt', var_export($_SESSION['token'], true));
        return $client;
    }

    protected function getConfig() {
        $json = $_ENV['CALENDAR_JSON_KEYS'];
        return json_decode($json, true);
    }
}
