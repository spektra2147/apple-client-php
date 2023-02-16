# Apple Client PHP
It provides a php service where you can use auth services for Apple devices.

## How to remove Apple Signed Application Account

Please make sure you have the following information to use the library.

- key_path : Key file "xxxxx.p8" given to you by Apple
- teamID(App ID Prefix) : You can find it in the information you created in the App ID field on the Certificates, Identifiers and Profiles screen.
- clientID (Bundle ID or Client ID or Services ID Ex: com.mapilio.main) : You can find it in the information you created in the App ID field on the Certificates, Identifiers and Profiles screen. 
- keyID : The key ID generated with your xxxx.p8 file. It is located in the file name with the extension xxx.p8, which you have received from your Apple account.


### Create a Private Key (xxx.p8 file and key ID) for Client Authentication

Rather than using simple strings as OAuth client secrets, Apple has decided to use a public/private key pair, where the client secret is actually a signed JWT. This next step involves registering a new private key with Apple.

1. Certificates, Identifiers & Profiles screen, choose Keys from the side navigation.
2. Click the blue plus icon to register a new key. Give your key a name, and check the Sign In with Apple checkbox.
3. Click the Configure button and select your primary App ID you created earlier.
4. Apple will generate a new private key for you and let you download it only once. Make sure you save this file, because you won’t be able to get it back again later! The file you download will end in .p8


### This library provides the following services.

- Generate Client Secret
- Auth
- Revoke



You can review this example usage

      $appleClient = new \VedatAkdogan\AppleClient\AuthApple('xxxxxxxx.p8','XXXXXXXXXX','com.mapilio.main','XXXXXXXXXX');
      $remove_apple_account = $appleClient->revoke($request->auth_code);


For more, visit the Apple Official Developer page.

https://developer.apple.com/documentation/sign_in_with_apple/generate_and_validate_tokens
