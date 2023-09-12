<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;

class FitbitAuthService {

    protected $user;
    protected \App\Models\FitbitAuth|null $fitbit_auth;
    protected $authUrl = 'https://www.fitbit.com/oauth2/authorize';
    protected $client_id = "23RDTX";
    protected $scope = "weight";

    function __construct() {
        $this->user = Auth::user();
        $this->fitbit_auth = \App\Models\FitbitAuth::where('user_id','=',$this->user->id)->first();
        //If the auth exists but is past expiry, refresh it!
        if($this->fitbit_auth && $this->fitbit_auth->expires_at < now()) {
            $this->fitbit_auth = $this->refreshAccessToken();
        }
    }

    public function getFitbitAuth() {
        return $this->fitbit_auth;
    }

    public function deleteFitbitAuth() {
        return $this->fitbit_auth?->delete();
    }

    /*
    * Submits the authorisation request for fitbit API access
    */
    public function requestAuthorisation() {

        //We first check for an existing authorisation. If this is their first time, we need to check!
        if($this->getFitbitAuth() == null) {
            $verifier = $this->generateCodeVerifier();
            $this->fitbit_auth = new \App\Models\FitbitAuth(['user_id' => Auth::user()->id, 'code_verifier' => $verifier]);
            $this->fitbit_auth->save();
        }

        //Get authorisation request API call/redirect body
        $body = $this->createRequestAuthorisationBody();

        //Take user to authorisation page
        return redirect()->away($this->authUrl .'?'. http_build_query($body));
    }

    /*
    * After approval is given, Fitbit will redirect back to this URL. We capture this and then execute the exchange 
    * between Auth code and access/refresh tokens
    * @param $code : string - Returned authorisation code from Fitbit API upon approval from user
    */
    public function codeExchange($code) {

        //If there is no auth object at this point, we cannot proceed.
        if($this->getFitbitAuth() == null) {
            \App::abort(401);
        }
        
        //Create code exchange body for API call to retrieve access/refresh
        $request_body = $this->createCodeExchangeBody($code);

        //Execute API call with body above
        $server_output = $this->codeExchangeAPiCall($request_body);
        if(isset($server_output->success) && $server_output->success == false) {
            return $this;
        }

        //Store resulting access token in auth table
        $this->fitbit_auth->access_token = $server_output->access_token;
        $this->fitbit_auth->refresh_token = $server_output->refresh_token;
        $this->fitbit_auth->fitbit_user_id = $server_output->user_id;
        $this->fitbit_auth->expires_at = new \DateTime('+'.$server_output->expires_in." seconds");
        $this->fitbit_auth->json = json_encode($server_output);
        $this->fitbit_auth->save();

        return $this;

    }

    public function refreshAccessToken() {
        //If there is no auth object at this point, we cannot proceed. Also if there is no refresh token.
        if($this->getFitbitAuth() == null || $this->getFitbitAuth()->refresh_token == null) {
            \App::abort(401);
        }

        //Otherwise we work to refresh the Apitoken
        $request_body = $this->createAuthRefreshBody();
        $server_output = $this->codeExchangeRefreshApiCall($request_body);
        
        //Handle potential errors before setting tokens

        return $this->getFitbitAuth()->setTokens($server_output);

    }

    private function createRequestAuthorisationBody() {

        //Forming request body according to fitbit API
        $body = array();
        $body['client_id'] = $this->client_id;
        $body['scope'] = $this->scope;
        $body['code_challenge'] = $this->generateCodeChallenge($this->getFitbitAuth()->getCodeVerifier());
        $body['code_challenge_method'] = "S256";
        $body['response_type'] = "code";

        return $body;

    }

    private function createCodeExchangeBody($code) {
        $postBody = array();
        $postBody['client_id'] = $this->client_id;
        $postBody['expires_in'] = 2880;
        $postBody['code'] = $code;
        $postBody['code_verifier'] = $this->getFitbitAuth()->getCodeVerifier();
        $postBody['grant_type'] = 'authorization_code';
        return $postBody;
    }

    private function createAuthRefreshBody() {
        $postBody = array();
        $postBody['client_id'] = $this->client_id;
        $postBody['expires_in'] = 2880;
        $postBody['refresh_token'] = $this->getFitbitAuth()->getRefreshToken();
        $postBody['grant_type'] = 'refresh_token';
        return $postBody;
    }

    private function codeExchangeApiCall($requestBody) {
        
        $ch = curl_init();
        //Building token verification API call
        curl_setopt($ch, CURLOPT_URL,"https://api.fitbit.com/oauth2/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        curl_setopt($ch, CURLOPT_POSTFIELDS, 
                  http_build_query($requestBody));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $server_output = json_decode($server_output);

        return $server_output;

    }

    private function codeExchangeRefreshApiCall($requestBody) {
        $postBody = $this->createAuthRefreshBody();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.fitbit.com/oauth2/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, 
                  http_build_query($postBody));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        curl_close($ch);
        $server_output = json_decode($server_output);
        return $server_output;
    }

    /*
    * Code and challenge generators. 
    *
    */

    private function generateCodeVerifier() {
        //Generate cryptographically random value between 43-128 characters
        return substr(md5(rand()), 0, 14).substr(md5(rand()), 0, 14).substr(md5(rand()), 0, 14).substr(md5(rand()), 0, 14);
    }

    private function generateCodeChallenge($str) {
        return rtrim(strtr(base64_encode(pack('H*', hash('SHA256',$str))), '+/', '-_'),'=');
    }
}

?>