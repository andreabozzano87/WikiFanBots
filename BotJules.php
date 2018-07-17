<?php
	require ('WikiFanResearchBot.php');
	$Botusr='edit_with_username';
	$Botpwd='edit_with_password';
	$wiki='address/YourWiki';
	echo "

__________          __         ____.       .__                   
\______   \  ____ _/  |_      |    | __ __ |  |    ____    ______
 |    |  _/ /  _ \\   __\     |    ||  |  \|  |  _/ __ \  /  ___/
 |    |   \(  <_> )|  |   /\__|    ||  |  /|  |__\  ___/  \___ \ 
 |______  / \____/ |__|   \________||____/ |____/ \___  >/____  >
        \/                                            \/      \/ 

";
	$BotJules=new WikiFanResearchBot($Botusr,$Botpwd,$wiki);
	
	$Pagine=array(//list of pages to extract); 
	
	//login
	$ch=curl_init();
	$logreq="$wiki/api.php?action=query&meta=tokens&type=login&format=json";
	curl_setopt($ch,CURLOPT_URL,$logreq);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response=curl_exec($ch);
    curl_close($ch);
    $jsonresponse=json_decode($response,true);
    $logintoken=urlencode($jsonresponse['query']['tokens']['logintoken']);
    $ContentType="application/x-www-form-urlencoded";
    
    //Fetchato il token per login, devo mandare richiesta post
    $chp=curl_init();
    $postdata="action=login&lgname=$Botusr&lgpassword=$Botpwd&lgtoken=$logintoken&format=json";
			  curl_setopt($chp, CURLOPT_URL, "$wiki/api.php");
              curl_setopt($chp, CURLOPT_POST, 1);
              curl_setopt($chp, CURLOPT_COOKIEJAR, 'cookie.txt');
              curl_setopt($chp, CURLOPT_COOKIEFILE, 'cookie.txt');
              curl_setopt($chp, CURLOPT_USERAGENT, $Botusr);//'WikiFanResearchBot 1.0');
              curl_setopt($chp, CURLOPT_POSTFIELDS, $postdata);
              curl_setopt($chp, CURLOPT_HTTPHEADER, array("Content-Type: $ContentType;charset=UTF-8"));
              curl_setopt($chp, CURLOPT_HEADER, false);
              curl_setopt($chp, CURLOPT_RETURNTRANSFER, true);
			  $response=curl_exec($chp);
			  curl_close($chp);
    $postresponsedec=json_decode($response,1);
	$edittoken=$BotJules->fetchedit();
	$GLOBALS['csrftoken']=$edittoken;
	if ($postresponsedec['login']['result']=='Success'){
	echo "Login andato bene!\n";
	} else { 
		echo "Login fallito: ".$postresponsedec['login']['result']."\n";
	}

foreach ($Pagine as $page) {
	$url="http://atlasgeneticsoncology.org/Genes/$page.html"; //url on Atlas of the gene page
	//Here you have to call all the actions you need the bot for. They can be found in 
	//WikiFanResearchBot.php file

}

	//logout
	$urlogout='localhost/WikiFanResearch/api.php?action=logout&format=json';
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $urlogout);
	curl_setopt($ch, CURLOPT_COOKIEJAR,'cookie.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE,'cookie.txt');
	curl_setopt($ch, CURLOPT_USERAGENT, $Botusr);
	curl_setopt($ch, CURLOPT_HTTPHEADER,  array("Content-Type: $ContentType;charset=UTF-8"));
	curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response=curl_exec($ch);
	curl_close($ch);
	$rdec=json_decode($response,true);
?>
