<?php

    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;

    require_once "phpmailer/Exception.php";
    require_once "phpmailer/PHPMailer.php";
    require_once "phpmailer/SMTP.php";
     
    //-------------Récupérer les états de livraison du colis Colissimo CB668226635FR-----------------------------------------

    $numColi = "CB668226635FR";
    $url = "https://api.laposte.fr/suivi/v2/idships/". $numColi ."?lang=fr_FR";
    $okapiKey = "sCkBhcqaVK3XfaLXx5hLutvJ628Iay3mHyIAr6nEZATRLDITZQyvJfYEbA9lKFn9";

    $header = array("Accept: application/json", "X-Okapi-Key: " . $okapiKey);

    $curl = curl_init();
	try {
		curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec($curl);

	    if (curl_errno($curl)) {
			echo curl_error($curl);
			die();
		}
		
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($http_code == intval(200)){
			echo "Ressource valide <br>";
		}
		else{
			echo "Ressource introuvable : " . $http_code . "<br>";
		}
	} catch (\Throwable $th) {
		throw $th;
	} finally {
		curl_close($curl);
	}

    $response = json_decode($response);

    //-------------Envoie du mail----------------------------------------------------------------------------

    // $mail = new PHPMailer(true);

    // try{

    //     //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
    //     $mail->isSMTP();
    //     $mail->Host = "localhost";
    //     $mail->Port = 1025;

    //     $mail->CharSet = "utf-8";

    //     $mail->addAddress("votrecoliestillivre@gmail.com");
        
    //     $mail->setFrom("no-reply@mail.com");

    //     $mail->Subject = "Reception coli" ;

    //     if($response->shipment->isFinal){
    //         $message="Votre coli numero ".$numColi." à été livré."; 
    //         $mail->addAttachment("attachment/batum-1.jpg");
    //     }else{
    //         $message="Votre coli numero ".$numColi." est toujours en cours de livraison."; 
    //     }
    //     $mail->Body = $message;

    //     $mail->send();
    //     echo " Message envoyé";
    
    // }catch(Exception){
    //     echo " Message non envoyé. Erreur: {$mail->ErrorInfo}";
    // }


    //-------------Enregistrement des états sur un csv----------------------------------------------------------------------------

    $csv = 'etatsColi.csv';
    
    $file_pointer = fopen($csv, 'w');

    foreach ($response->shipment->event as $key => $value) {
        fputcsv($file_pointer, [$value->code,$value->date,$value->label]);
    }

    fclose($file_pointer);