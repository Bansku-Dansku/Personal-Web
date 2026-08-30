<?php
# Variables, etc

require "../../env.php";

$email = $phone = $text = "";
$success = 0;

# Getting data & validating it

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["email"])) {
    $success = $success+1;
  } else {
    $email = validate_input($_POST["email"]);
    // check if e-mail address is well-formed
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $success = $success+1;
    }
  }
  if (empty($_POST["phone"])) {
    $phone = "";
  } else {
    $phone = validate_input($_POST["phone"]);
  }
  if (empty($_POST["text"])) {
    $success = $success+1;
  } else {
    $text = validate_input($_POST["text"]);
  }
}

function validate_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

# Sending the webhook

if ($success == 0) {
    $headers = array('Content-Type: application/json'); 
    $msg = [
         "content" => "",
         "embeds" => [
            [
                "title" => "New contact",
                "type" => "rich",
                "description" => "It is a scammer or an actual person",
                "url" => "https://banskudansku.net",
                "color" => hexdec( "0D0DFF" ), //red color for the title
                "fields" => [
                    [
                        "name" => "Email",
                        "value" => !empty($email) ? $email : "Not provided",
                        "inline" => true
                    ],
                    [
                        "name" => "Phonenumber",
                        "value" => !empty($phone) ? $phone : "Not provided",
                        "inline" => true
                    ],
                    [
                        "name" => "Text",
                        "value" => !empty($text) ? $text : "Not provided"
                    ]
                ]
            ]
        ],
    ];  


    # I have some idea how this works but not fully
    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, $webhook_url );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $msg ) );
    $response = curl_exec( $ch );
    curl_close( $ch );

    header("Location: /?status=success#contact");
    exit();
} else {
    header("Location: /?status=error#contact");
    exit();
}