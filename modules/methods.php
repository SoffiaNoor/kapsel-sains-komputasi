<?php

class RSA{
    private $publicKey;
    private $privateKey;

  function __construct() {}

  public function generateKeypair() {
    $config = array(
        'config' => 'C:\xampp\htdocs\tubes\openssl.cnf',
        'default_md' => 'sha512',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    );

    $keypair = openssl_pkey_new($config); //This is actually generates private key
    $exportedKey = openssl_pkey_export($keypair, $privkey, null, $config);
    $this->privateKey = $privkey; //get private key
    $pubkey = openssl_pkey_get_details($keypair);
    $this->publicKey = $pubkey['key']; //get public key
    
  }

  public function getPrivateKey() {
    return $this->privateKey;
  }

  public function getPublicKey() {
    return $this->publicKey;
  }

  private function encrypt2($message) {
    openssl_public_encrypt($message, $encryptedData, $this->publicKey);
    return $encryptedData;
  }

  public function encryptAndEncode($message) {
    $encryptedMsgInBase64 = base64_encode($this->encrypt2($message));
    return $encryptedMsgInBase64;
  }
  private function decrypt2($cipherText) {
    openssl_private_decrypt($cipherText, $decryptedData, $this->privateKey);
    return $decryptedData;
  }

  public function decodeAndDecrypt($cipherText) {
    $decodedEncryptedMsg = base64_decode($cipherText);
    return $this->decrypt2($decodedEncryptedMsg);
  }

  public function decodeAndDecryptWithPrivateKey($cipherText, $privateKey) {
    $decodedEncryptedMsg = base64_decode($cipherText);
    openssl_private_decrypt($decodedEncryptedMsg, $decryptedData, $privateKey);
    return $decryptedData;
  }

}

class AES{
      //128 bits, key = 128 (least security) or 192 or 256 (highest security) bits [SOLVED] ==> str_split
      //Text XOR 
    // Keybikinan kami
    private $iv;
    private $tag;
    public $ciphertext;
    public function generateIV() {
        $this->iv = openssl_random_pseudo_bytes(16); // 128 bits
        return $this->iv;
    }
     
    public function encryptAES256GCM($plaintext, $key, $iv) {
        $this->ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $this->iv, $this->tag);
        return [$this->ciphertext, $this->tag];
    }
    
    // public function encryptAES256GCMAndEncode($message, $key) {
    //     $encryptedMsgInBase64 = base64_encode($this->encryptAES256GCM($message, $key, $this->iv));
    //     return $encryptedMsgInBase64;
    //  }
    
    public function getCipherText() {
        return $this->ciphertext;
    }

      public function getTag() {
        return $this->tag;
    }

    public function decryptAES256GCM($ciphertext, $tag, $key, $iv) {
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return $plaintext;
    }
    
}
?>