<?php
  require_once('../server/DB.php');
  require_once('../modules/methods.php');
  $db = DB::getInstance();

  $senderId = $_POST['sid'];
  $receiverId = $_POST['rid'];
  $message = $_POST['msg'];
  $encMethodId = $_POST['eid'];
  $isFile = $_POST['isFile'];
  $fileName = $_POST['fileName'];

  // $keyAes = "";
  // $cipherAes = "";
  // $senderKeyAes = "";
  // $receiverKeyAes = "";
// bikinan kami
  // $newKey = "";
  $newKeyAes = "";
  $ivEncoded = "";
  $cipher2Aes = "";
  $tagEncoded = "";

  $eMessage = "";

  $pub_key="";
  $priv_key = "";
  $encryptedKey = "";
  $encryptedMsg = "";

  // echo $message;

  if($encMethodId == 0){
    $rsa = new RSA();
    //bikinan kami
    $rsa->generateKeypair(); //ntar coba tak masukin ke konstruktor aee
    $priv_key = $rsa->getPrivateKey();
    $pub_key = $rsa->getPublicKey();
    //mungkin generate aes di sini + iv
    $aes = new AES();
    $strongRandom = true;
    $newKeyAes = openssl_random_pseudo_bytes(32, $strongRandom); // 256 bits
    $ivAes = $aes->generateIV(); // 128 bits
    //encrypt msg dgn aes
    $aes->encryptAES256GCM($message, $newKeyAes, $ivAes);
    //simpen tag
    $cipherText = $aes->getCipherText(); //ambil cipher text
    $tag = $aes->getTag();
    //encode to base64 before storing them to db
    $ivEncoded = base64_encode($ivAes);
    $cipher2Aes = base64_encode($cipherText); // TO DO: change cipher2Aes to encodedCipherText
    $tagEncoded = base64_encode($tag);
    //encrypt kunci aes dgn pub_key
    $encryptedKey = $rsa->encryptAndEncode($newKeyAes);

    $encryptedMsg = $cipher2Aes;
    //bikinan kami
}

  $query = "SELECT token from login_details WHERE uid=$senderId";
  $result = mysqli_query($db, $query);
  $row = mysqli_fetch_assoc($result);
  $senderToken = $row['token'];

  $query = "SELECT token from login_details WHERE uid=$receiverId";
  $result = mysqli_query($db, $query);
  $row = mysqli_fetch_assoc($result);
  $receiverToken = $row['token'];

  $query = "INSERT INTO chat (sender_id, receiver_id, message, message2, iv, tag, 
                              is_file, file_name, enc_method, s_token, r_token) 
            VALUES($senderId, $receiverId, 'lama punya', '$encryptedMsg', '$ivEncoded', 
                    '$tagEncoded', '$isFile', '$fileName', $encMethodId, '$senderToken', '$receiverToken')";
  if($db->query($query) !== true){
    echo "Messsage Has not sent due to an error -1. ".mysqli_error($db);
  }
  else{
    $lastChatId = $db->insert_id;
    if($encMethodId == 0){
      $query = "INSERT INTO rsa (message_id, d, n, every_separate, privateKey, publicKey, encryptedKey) 
                VALUES ($lastChatId, 'yang lama', 'yang lama', 'yang lama', '$priv_key', '$pub_key', 
                        '$encryptedKey')";
      if($db->query($query) !== true){
        echo "Messsage Has not sent due to an error 1. ".mysqli_error($db);
      }
    }
  }
?>