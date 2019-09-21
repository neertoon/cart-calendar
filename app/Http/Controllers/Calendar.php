<?php
namespace App\Http\Controllers;

use App\Services\Wydruk\GeneratorDom;
use App\User;
use App\Http\Controllers\Controller;

class Calendar extends Controller
{
    const APPLICATION_ID = '973852827368-4981aoak1r7car10lskp4jau4bm61khv.apps.googleusercontent.com';
    private $application_redirect_url;
    public function __construct() {
        $this->application_redirect_url = 'http://'.$_SERVER['HTTP_HOST'].'/server.php';
//        $this->application_redirect_url = 'server.php';
    }

    /**
     * Show the profile for the given user.
     *
     * @param int $id
     * @return View
     */
    public function index()
    {
        $data = array_merge($_GET, $_POST);
        file_put_contents('server_data.txt', var_export($data, true));

        if(!empty($_GET['code'])) {
            $client = new \Google_Client();
            $client->setApplicationName('cart-calendar-253012');
            $client->setScopes(\Google_Service_Calendar::CALENDAR);
//            $client->setAuthConfig('credentials.json');
            $client->setAuthConfig($this->getConfig());
            $client->setAccessType('online');
            $cred = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            file_put_contents('server_token.txt', var_export($cred, true));

            $service = new \Google_Service_Calendar($client);
            // Print the next 10 events on the user's calendar.
            $calendarId = 'primary';
            $optParams = array(
                'maxResults' => 10,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => date('c'),
            );
            $results = $service->events->listEvents($calendarId, $optParams);
            $events = $results->getItems();

            $calendars = $service->calendarList->listCalendarList();
            echo '<pre>';
            var_export($calendars);
            echo '</pre>';

//            var_export($events);
        }

        $linkToSignIn  = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode($this->application_redirect_url) . '&response_type=code&client_id=' . self::APPLICATION_ID . '&access_type=online';

        $data = [
            'sign_in' => $linkToSignIn,
        ];

        $this->showView($data);
    }

    public function test() {
        echo 'TEST';

        $dane = [
            ['zmienna' => 'sdfds', 'pole1' => 'taki tekst', 'pole2' => 'inny gosciu'],
            ['zmienna' => '', 'pole1' => 'koelny', 'pole2' => 'wiersz'],
        ];

        $wydruk = new GeneratorDom();
        $wydruk->ustawNazweWyjsciowegoPliku('szablon_out.odt');
        return $wydruk->generuj($dane, 'szablon.odt');
    }

    protected function showView($data) {
        $out = <<<OUT

<h1>Welcom on Cart Calendar V18-2032</h1>
<h2>Please <a href="{$data['sign_in']}">SIGN IN</a></h2>

OUT;

        echo $out;
    }

    protected function getConfig() {
        $json = '{"web":{"client_id":"973852827368-4981aoak1r7car10lskp4jau4bm61khv.apps.googleusercontent.com","project_id":"cart-calendar-253012","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://oauth2.googleapis.com/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_secret":"WchllqHP2kBNn1Gvqb82DZ6f","redirect_uris":["http://cart-calendar.azurewebsites.net/server.php"]}}';
        return json_decode($json, true);
    }
}
