<?php
namespace App\Http\Controllers;

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

        $linkToSignIn  = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode($this->application_redirect_url) . '&response_type=code&client_id=' . self::APPLICATION_ID . '&access_type=online';

        $data = [
            'sign_in' => $linkToSignIn,
        ];

        $this->showView($data);
    }

    protected function showView($data) {
        $out = <<<OUT

<h1>Welcom on Cart Calendar</h1>
<h2>Please <a href="{$data['sign_in']}">SIGN IN</a></h2>

OUT;

        echo $out;
    }
}
