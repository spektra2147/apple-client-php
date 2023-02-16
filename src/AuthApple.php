<?php

namespace VedatAkdogan\AppleClient;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;

class AuthApple
{

    /**
     * Private key file from Apple
     * Ex: 'AuthKey_XXXXXXXXXX.p8' in laravel /public path
     * https://support.mobiroller.com/en/knowledgebase/5-how-to-create-a-key-file-p8-on-apple-to-send-push-notifications/
     */
    public $key_path = '';

    /**
     * Your 10-character Team ID or App ID Prefix
     */
    public $teamID = '';

    /**
     * Bundle ID or Client ID or Services ID
     * Ex: com.mapilio.main
     */
    public $clientID = '';

    /**
     * Find the 10-char Key ID value from the portal
     * It is located in the file name with the extension xxx.p8,
     * which you have received from your Apple account.
     * Ex: AuthKey_XXXXXXXXXX.p8
     */
    public $keyID = '';

    public $clientSecret = '';

    public function __construct($key_path, $teamID, $clientID, $keyID)
    {
        $this->key_path = $key_path;
        $this->teamID = $teamID;
        $this->clientID = $clientID;
        $this->keyID = $keyID;
    }

    public function generateClientSecret()
    {
        $algorithmManager = new AlgorithmManager([new ES256()]);
        $jwsBuilder = new JWSBuilder($algorithmManager);

        $jws = $jwsBuilder
            ->create()
            ->withPayload(json_encode([
                'iat' => time(),
                'exp' => time() + 86400 * 180,
                'iss' => $this->teamID,
                'aud' => 'https://appleid.apple.com',
                'sub' => $this->clientID
            ]))
            ->addSignature(JWKFactory::createFromKeyFile($this->key_path), [
                'alg' => 'ES256',
                'kid' => $this->keyID
            ])
            ->build();

        $serializer = new CompactSerializer();
        $token = $serializer->serialize($jws, 0);

        $this->clientSecret = $token;

        return $token;
    }

    /**
     * @param $authorizationCode
     *  You can get the 'authorizationCode' value from the device
     *  you are logged into as a response parameter.
     */

    public function token($authorizationCode)
    {
        $this->generateClientSecret();

        $params = [
            "client_id" => $this->clientID,
            "client_secret" => $this->clientSecret,
            "code" => $authorizationCode,
            "grant_type" => 'authorization_code'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://appleid.apple.com/auth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);

        $rawResponse = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($rawResponse);
        curl_close($ch);

        if ($httpcode != 200) {
            throw new \Exception($response->error_description, $httpcode);
            die;
        }

        return $response;
    }

    /**
     * @param $authorizationCode
     *  You can get the 'authorizationCode' value from the device
     *  you are logged into as a response parameter.
     */
    public function generateAccessToken($authorizationCode)
    {
        $token = $this->token($authorizationCode);

        return $token->access_token;
    }

    public function revoke($authorizationCode)
    {
        $access_token = $this->generateAccessToken($authorizationCode);

        $params = [
            "client_id" => $this->clientID,
            "client_secret" => $this->clientSecret,
            "token" => $access_token,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://appleid.apple.com/auth/revoke');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_POST, 1);

        $rawResponse = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = $rawResponse;
        curl_close($ch);

        if ($httpcode == 200) {
            return true;
        } else {
            throw new \Exception(trans('streams::error.'.$httpcode.'.name'), $httpcode);
            die;
        }

        return false;
    }
}
