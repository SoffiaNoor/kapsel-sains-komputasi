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

  $keyAes = "";
  $cipherAes = "";
  $senderKeyAes = "";
  $receiverKeyAes = "";
// bikinan kami
  $newKey = "";
  $newKeyAes = "";
  $ivEncoded = "";
  $cipher2Aes = "";
  $tagEncoded = "";

  $eMessage = "";

  $keyDes = "";
  $cipherDes = "";
  $senderKeyDes = "";
  $receiverKeyDes = "";

  $c1Gamal = "";
  $xaGamal = "";
  $qGamal = "";
  $esGamal = "";

  $p = 0;
  $q = 0;
  $n = 0;
  $z = 0;
  $e = 0;
  $d = 0;
  $c = "";
  $everySeparate = "";
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
  else if($encMethodId == 1){
    $query = "SELECT * FROM session 
              WHERE (sender_id=$senderId OR sender_id=$receiverId) AND (receiver_id=$receiverId OR receiver_id=$senderId)";
    $result = mysqli_query($db, $query);
    $numOfRows = mysqli_num_rows($result);

    if($numOfRows > 0){
      $row = mysqli_fetch_assoc($result);
      $senderKeyAes = $row['sender_key'];
      $receiverKeyAes = $row['receiver_key'];
      $newKey =  $row['new_key'];//bikinan kami
      $newKeyAes = hex2bin($newKey); //bikinan kami
      // $decodednewKeyAes = hexbin

      $aes = new AES();
      $messageAes = $message;

      $messageAes = str_split($messageAes,16);
      for($i=0; $i<count($messageAes); $i++)
      {
          $messageAes[$i] = str_pad($messageAes[$i],16,'#',STR_PAD_LEFT);
      }

      for($i=0 ; $i<count($messageAes) ; $i++)
      {
          $cipher = $aes->AES_ENCTYPT($messageAes[$i], $senderKeyAes);
          // $cipher = hex2bin($cipher);  MySQL can not store it
          $cipherAes .= $cipher;
      }

      // bikinan kami
      $ivAes = $aes->generateIV();
      // $cipherText = $aes->encryptAES256GCM($message, $newKeyAes, $ivAes);
      $aes->encryptAES256GCM($message, $newKeyAes, $ivAes);
      $cipherText = $aes->getCipherText();
      $tag = $aes->getTag();

      // encoded to base64 to store them to database
      $ivEncoded = base64_encode($ivAes);
      $cipher2Aes = base64_encode($cipherText);
      // $cipher2Aes = $cipherText;
      $tagEncoded = base64_encode($tag);

      $eMessage = $cipherAes;
      $encryptedMsg = $cipher2Aes;
    }
  }
  else if($encMethodId == 2){
    $rsa = new RSA();
    $pRsa = $rsa->findRandomPrime(-1);
    $qRsa = $rsa->findRandomPrime($pRsa);
    $nRsa = $rsa->compute_n($pRsa, $qRsa);
    $zRsa = $rsa->eular_z($pRsa, $qRsa);
    $eRsa = $rsa->find_e($zRsa);
    $dRsa = $rsa->find_d($eRsa, $zRsa);
    //bikinan kami
    $rsa->generateKeypair(); //ntar coba tak masukin ke konstruktor aee
    $priv_key = $rsa->getPrivateKey();
    $pub_key = $rsa->getPublicKey();
    $encryptedMsg = $rsa->encryptAndEncode($message);
    // $priv_key = "kunci privat";
    // $pub_key = "kunci publik";
    //bikinan kami
    list($cRsa, $everySeparateRsa) = $rsa->encrypt($message, $eRsa, $nRsa);
    $eMessage = $cRsa;
  }
  else if($encMethodId == 3){
    $c1Gamal = $_POST['c1Gamal'];
    $xaGamal = $_POST['xaGamal'];
    $qGamal = $_POST['qGamal'];
    $everySeparateGamal = $_POST['esGamal'];
    $eMessage = $message;
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
    else if($encMethodId == 1){
      // $query = "INSERT INTO aes (message_id, cipher, sender_key, receiver_key) 
      //           VALUES ($lastChatId, '$cipherAes', '$senderKeyAes', '$receiverKeyAes')";
      $query = "INSERT INTO aes (message_id, cipher, cipher2, sender_key, receiver_key, new_key, iv, tag) 
                VALUES ($lastChatId, '$cipherAes', '$cipher2Aes', '$senderKeyAes', '$receiverKeyAes', 
                        '$newKey', '$ivEncoded', '$tagEncoded')";
      if($db->query($query) !== true){
        echo "Messsage Has not sent due to an error 1. ".mysqli_error($db);
      }
    }
    else if($encMethodId == 2){
      $query = "INSERT INTO rsa (message_id, d, n, every_separate, privateKey, publicKey) 
                VALUES ($lastChatId, '$dRsa', '$nRsa', '$everySeparateRsa', '$priv_key', '$pub_key')";

      if($db->query($query) !== true){
        echo "Messsage Has not sent due to an error 2. ".mysqli_error($db);
      }
    }
    else if ($encMethodId == 3){
      $query = "INSERT INTO gamal (message_id, c1, xa, q, every_separate) VALUES ($lastChatId, '$c1Gamal', '$xaGamal', '$qGamal', '$everySeparateGamal')";
      if($db->query($query) !== true){
        echo "Messsage Has not sent due to an error 3. ".mysqli_error($db);
      }
    }
  }
?>