<?php
	require ('WikiFanResearchBot.php');
	$Botusr='Root@BotJules';
	$Botpwd='r6i6e3qhipddv9p8fn4v1i0k3eb39vsu';
	$wiki='localhost/WikiFanResearch';
	echo "

__________          __         ____.       .__                   
\______   \  ____ _/  |_      |    | __ __ |  |    ____    ______
 |    |  _/ /  _ \\   __\     |    ||  |  \|  |  _/ __ \  /  ___/
 |    |   \(  <_> )|  |   /\__|    ||  |  /|  |__\  ___/  \___ \ 
 |______  / \____/ |__|   \________||____/ |____/ \___  >/____  >
        \/                                            \/      \/ 

";
	$BotJules=new WikiFanResearchBot($Botusr,$Botpwd,$wiki);
	
	//come creo array? Prendo l'elenco delle pagine, http://atlasgeneticsoncology.org//Genes/, 
	//e quindi il collegamento alla pagina html,
	//infine lo inserisco nell'array da dare al bot.
	$Pagine=array("FACCID101","FADID103","FANCEID293","FANCGID295","GC_FAAP24"); 
	
	//login
	$ch=curl_init();
	$logreq='localhost/WikiFanResearch/api.php?action=query&meta=tokens&type=login&format=json';
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
			  curl_setopt($chp, CURLOPT_URL, 'localhost/WikiFanResearch/api.php');
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
	
	
	$page='FACCID101';
//foreach ($Pagine as $page) {
	$url="http://atlasgeneticsoncology.org/Genes/$page.html"; 
	//Azioni
	$PaginaGene=$BotJules->retrieveINFOgene($url,$page);
	//$Creazione=$BotJules->create_section($page,$PaginaGene,$GLOBALS['NomeGene'],$wiki);
	//echo $PaginaGene;
	//$section='Description';
	//$newcontent='Description Updates';
	//$pg=$BotJules->EditContent($page,$section,$newcontent);
//}

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
