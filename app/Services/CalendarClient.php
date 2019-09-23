<?php


namespace App\Services;


class CalendarClient {
    public function get($code = '') {

        $client = new \Google_Client();
        $client->setApplicationName('cart-calendar-253012');
        $client->setScopes(\Google_Service_Calendar::CALENDAR);
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
        $json = '{"web":{"client_id":"973852827368-4981aoak1r7car10lskp4jau4bm61khv.apps.googleusercontent.com","project_id":"cart-calendar-253012","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://oauth2.googleapis.com/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_secret":"WchllqHP2kBNn1Gvqb82DZ6f","redirect_uris":["http://cart-calendar.azurewebsites.net/index.php/cart"]}}';
        return json_decode($json, true);
    }
}
