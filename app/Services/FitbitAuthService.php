<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;

class FitbitAuthService {

    protected $user;
    protected $authUrl = 'https://www.fitbit.com/oauth2/authorize';

    function __construct($user) {
        $this->user = $user;
    }

    public function generateCodeVerifier() {
        //Generate cryptographically random value between 43-128 characters
        return substr(md5(rand()), 0, 14).substr(md5(rand()), 0, 14).substr(md5(rand()), 0, 14).substr(md5(rand()), 0, 14);
    }

    private function generateCodeChallenge($str) {
        return rtrim(strtr(base64_encode(pack('H*', hash('SHA256',$str))), '+/', '-_'),'=');
    }

    public function requestAuthorisation() {

        $fitbitAuth = \App\Models\FitbitAuth::where('user_id','=',Auth::user()->id)->first();
        if(!isset($fitbitAuth)) {
            $verifier = $this->generateCodeVerifier();
            $fitbitAuth = new \App\Models\FitbitAuth(['user_id' => Auth::user()->id, 'code_verifier' => $verifier]);
            $fitbitAuth->save();
        }

        $body = array();
        $body['client_id'] = "23RDTX";
        $body['scope'] = "weight";
        $body['code_challenge'] = $this->generateCodeChallenge($fitbitAuth->getCodeVerifier());
        $body['code_challenge_method'] = "S256";
        $body['response_type'] = "code";

        return redirect()->away($this->authUrl .'?'. http_build_query($body));
    }

    public function captureRedirect($code) {
        $authObject = \App\Models\FitbitAuth::where('user_id','=',Auth::user()->id)->first();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.fitbit.com/oauth2/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        $postBody = array();
        $postBody['client_id'] = "23RDTX";
        $postBody['code'] = $code;
        $postBody['code_verifier'] = $authObject->getCodeVerifier();
        $postBody['grant_type'] = 'authorization_code';

        curl_setopt($ch, CURLOPT_POSTFIELDS, 
                  http_build_query($postBody));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $server_output = json_decode($server_output);

        $authObject->access_token = $server_output->access_token;
        $authObject->refresh_token = $server_output->refresh_token;
        $authObject->fitbit_user_id = $server_output->user_id;
        $authObject->json = json_encode($server_output);
        $authObject->save();

    }
}

?>