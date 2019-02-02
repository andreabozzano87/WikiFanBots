<?php
//      Welcome on WikiFanResearchBot.php!
//      Developed by andrea.bozzano87, WikiFanResearchBot is a collection of methods
//      for a simple Bot Class for MediaWiki Projects. In the original Project, this bot class was used to populate both WikiFan and 
//      WikiFanResearch, a wiki family of two wiki projects builded with MediaWiki and Semantic MediaWiki.
//      In this file you'll find the constructor of the class, the login function, the functions for
//      calling the API in GET and POST methods, creating and editing pages on the Wiki.
//      For simple use, include this file in a script and create a new object WikiFanResearchBot, passing the Wiki where the bot should
//      operate, the server address, and the MySQL database of the wiki.
//      Have fun with it!
//      Where to find MediaWiki --> https://www.mediawiki.org/
//      Where to find Semantic MediaWiki extension --> https://www.semantic-mediawiki.org
//      Other extensions are avaiable and free downloadable on MediaWiki website and GitHub.

class WikiFanResearchBot {
    public $epm=5;
    public $maxlag=5;
    public $wiki='yourwiki';
    public $username;
    public $password;
    public $lgtoken;
    public $host='localhost';
    public $db_name='wikifanresearchdb';
    
//  This is the constructor, it creates the istance of the object WikiFanResearchBot and makes him log into the wiki.
//  You have to pass the credentials, the wiki, the edit-per-minute ($epm) and the maximum latency with the server (i wrote 5 seconds)
//  WATCH OUT! the Bot should have an account on the wiki project and should be flagged as Bot user to let him do a lot of operations
//  in a very short time.
public function __construct($username,$password,$wiki,$epm=5,$maxlag=5) { 
        if (!isset($username) || !isset($password)) {
            die("r<br /> />\nErrore: credenziali non inserite!\r <br />\n");
            }
       $this->wiki=$wiki;
       $this->epm=60/$epm;
       $this->max_lag=$maxlag;
       $this->username=$username;
       $this->password=$password;
}

//  This function defines the default $wiki as WikiFanResearch
private function wiki($wiki) { 
        if ($wiki=="") {
            return $wiki="path to YourWiki";
            } 
            return $wiki;
}


//  This function can be used to make requests to the API with the GET method. You need to pass the url and the wiki.
    private function callAPI($wiki, $url, $format = "json") { 
          $wiki=$this->wiki;
          $maxlag=$this->max_lag;
          $url_ch = "$wiki.$url&maxlag=$maxlag&format=$format";
          $ch = curl_init();
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
              curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
              curl_setopt($ch, CURLOPT_URL, $url_ch);
          $response = curl_exec($ch);
          if (curl_errno($ch)) {
              return curl_error($ch);
          }
          curl_close($ch);
  }


//    This function can be used to make requests to the API with the POST method._exists You need to pass the url and the wiki.
    private function postAPI($wiki,$url,$postdata="",$ContentType="application/x-www-form-urlencoded") { 
        $BotUser=$this->username;
        $ch=curl_init();
              $url_post=$wiki.$url;
              curl_setopt($ch, CURLOPT_URL, $url_post);
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
              curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
              curl_setopt($ch, CURLOPT_USERAGENT, $BotUser);//'WikiFanResearchBot 1.0');
              curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
              curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: $ContentType;charset=UTF-8"));
              curl_setopt($ch, CURLOPT_HEADER, false);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $response=curl_exec($ch);
          $response_decoded=json_decode($response,TRUE);
        return $response_decoded;
}


//  This function retrieves the content of a page from the wiki and stores it in a variable, with format json, for later use.
    public function get_page($page,$wiki) {
        $url_query="api.php?action=query&titles=$page&prop=revisions&rvprop=content&format=json";
        $pagina=$this->callAPI($wiki,$url_query,"json");
        echo "l'url della query è: " .$url_query."\n";
        $pagina_decoded=json_decode($pagina,true);
        //var_dump($pagina_decoded); useful for debug, to see the decoded page in the output console
        return $pagina_decoded;
}

//  This function adds a section on a page, with the text you pass in the variable $text. You need to pass the page, 
//  the text you want to write, the title of the new section and the wiki.
    public function create_section($page, $text, $sectiontitle, $wiki = "") { 
    $edittoken=$GLOBALS['csrftoken'];
    $urlapi="/api.php";
    $postdata="format=json&action=edit&title=$page&section=new&sectiontitle=$sectiontitle&text=$text&token=".urlencode($edittoken);
    $pageedited=$this->postAPI($wiki,$urlapi,$postdata);
    if ($pageedited['edit']['result']=="Success") {
	echo "\nHo creato ".$GLOBALS['NomeGene']."...\n----------------8<-------------[ taglia qui ]------------------\n";
}
else {
	$pagedec=json_decode($pageedited,true);
	}
}

//  This function is used to fetch an edit token for the page you're gonna edit. It just sends a GET request for edit token.
  public function FetchEditToken($page) {
	  
    $wiki=$this->wiki;
    $pagetitle=str_replace(' ','_',$page);
    $url="http://$wiki/api.php?action=query&prop=info|revisions&intoken=edit&titles=$page&format=json";
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_COOKIEJAR,'cookie.txt');
    curl_setopt($ch,CURLOPT_COOKIEFILE,'cookie.txt');
    curl_setopt($ch,CURLOPT_URL,$url);
    $response=curl_exec($ch);
    $response_decoded=json_decode($response,true);
    $id_pagina=$this->controllaIDpagina($page);    
    $edittoken=$response_decoded["query"]["pages"]["$id_pagina"]["edittoken"];
    $response_decoded["query"]["pages"][$id_pagina]["edittoken"];
    $GLOBALS['edittoken']=$edittoken;
    return $edittoken;
}

//  This function executes queries on the database of the Wiki, just pass it the query you wanna execute.
public function eseguicomandoDB($query) {  
    $db_user=$this->username;
    $db_password=$this->password;
    $db_host=$this->host;
    $db_name=$this->db_name;
    $conn=new mysqli($db_host,$db_user,$db_password,$db_name);
if (!$conn)
    {
       die("Cannot connect to DB: <br />".mysql_error());
    }
    $result=mysqli_query($conn,$query);
    mysqli_close($conn);
    return $result;
}


//  This function gets the ID of the page you're editing, directly from the DB, given the title of the page
public function controllaIDpagina($page) { 
    $query="SELECT page_id FROM enpage WHERE page_title='$page'";
    $risultato=$this->eseguicomandoDB($query);
    if($risultato->num_rows>0)
    {
        $row=$risultato->fetch_assoc();
        return $row['page_id'];
    } 
    else
    {
        $row =-1;
        return $row;
    }
}


//  This function gets the text from a static html page, passed as string, and it's used to pass text to the wiki.
//  It first searches for a topic element in the page, and then it looks for the text to find from that point on, since the function
//  strpos() returns the first occurrence of the pattern you pass in the string.
public function testodaHTML($paginaHTML,$topic,$textofind,$stopchar='</TD>') {

  $paginaHTML=addslashes($paginaHTML);
  $posizionetopic=strpos($paginaHTML,$topic);
  $lung=strlen($textofind);
  $posizionetext=strpos($paginaHTML,$textofind,$posizionetopic)+$lung;
  $finetext=strpos($paginaHTML,$stopchar,$posizionetext);
  $lunghezzatext=$finetext-$posizionetext;
  $testofinale=substr($paginaHTML,$posizionetext,$lunghezzatext);
  if ($posizionetopic===FALSE){
	  $testofinale='To be completed';
  }

  return trim($testofinale);
}

//Not used. This function was intended to create a page with a template from an HTML page. See createsection()
public function CreaPaginaGene($url,$page) {
//TODO script the email part to get the email from the Atlas
    $wiki=$this->wiki;
    $AuthorName=$this->testodaHTML($url,"Written","</TD><TD>");
    echo "Nome: $AuthorName\n";
    $Quotation=$AuthorName;
    $Affiliation=$this->testodaHTML($url,$AuthorName,"<font size=-1>");
    echo "Affiliazione: $Affiliation\n";
    $Email="NonpresenteAtlas";
    $texttoadd="\{{AuthorInfoBox|Author Name=$AuthorName|Quotation=$Quotation|Affiliation=$Affiliation|Email address=$Email}}";
    
    //echo "i dati post con il template sono:\n".$texttoadd."\n"; useful for debugging in console
    
    $edittoken=$GLOBALS['edittoken'];
    $postdata="action=edit&title=$page&section=new&text=$texttoadd&token=".urlencode($edittoken)."&format=json";
    echo "i dati post sono: $postdata\n";
    
    }

//This function gets an HTML page and converts it to a string
public function HtmlToString($url) {
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_COOKIEJAR,'cookie.txt');
    curl_setopt($ch,CURLOPT_COOKIEFILE,'cookie.txt');
    curl_setopt($ch,CURLOPT_URL,$url);
    $response=curl_exec($ch);
    curl_close($ch);
    file_put_contents('paginaHtmlResponse.txt',$response);
    $paginaHTML=$response;
	
    return $response;
}

//  This function is used to fetch a CSRFToken with a get request to APIs
public function fetchedit(){
	$url='http://$wiki/api.php?action=query&meta=tokens&format=json';
	$ch = curl_init();
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
          curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
          curl_setopt($ch, CURLOPT_URL, ($url));
    $response = curl_exec($ch);
    curl_close($ch);
	$rdec=json_decode($response,true);
	$token=$rdec['query']['tokens']['csrftoken'];
	//echo "We are in fetchedit(), il token è: $token\n";
	return $token;
}

//This function uploads an image on the wiki, to use it in articles.
public function UploadImage($nomefile){
	$wiki=$this->wiki;
	$edittoken=$GLOBALS['csrftoken'];
	$ContentType="application/x-www-form-urlencoded";
	$EncodedFileUrl=urlencode('http://atlasgeneticsoncology.org//Genes/png/'.$nomefile);
	$postdata='action=upload&format=json&ignorewarnings=true&filename='.$nomefile.'&url='.$EncodedFileUrl.'&token='.urlencode($edittoken);
	$risp=$this->postAPI($wiki, '/api.php',$postdata);
}

//This function can discriminate between a text string or an image file, returning the wikitext syntax of the file
//uploaded in the wiki in case.
public function textorimage($paginaHTML,$startsearch,$topic,$stopsearch){
	$topic='DNA/RNA';
	$startsearch='</FONT><Strong></TD></TR>';
	$stopsearch='</CENTER></TD></TR>';
	$stringahtml=$this->testodaHTML($paginaHTML,$topic,$startsearch,$stopsearch);

	if (strpos($stringahtml,'<IMG SRC=')){
		$nomeimg=$this->testodaHTML($paginaHTML,$topic,'<IMG SRC=./png/','BORDER=1>');
		$uploaded=$this->UploadImage($nomeimg);
		$Description="[[File:$nomeimg|center|link=]]";
	}
	else
	{
		$Description=$this->testodaHTML($paginaHTML,'DNA/RNA</FONT>','Description</TD><TD>','</TD></TR>');
		$risultato=$Description;
	}
	return $Description;
}

//This function gets the string from HtmlToString($url) and retrieves informations for the templates AuthorInfoBox,
//GeneInfoBox and GenePage.
public function retrieveINFOgene($url) {
    $paginaHTML=$this->HtmlToString($url);
    //$ImplicatedIn=$this->getImplicatedIn($paginaHTML);
	
   //AuthorInfoBox
   $infoauthor="";
   $infoauthor="{{AuthorInfoBox|Author Name=";
   $AuthorName=$this->testodaHTML($paginaHTML,"<b>Written</b>","</TD><TD>","</TD></TR>");
   $infoauthor.=$AuthorName."|Quotation=";
   $Quotation=$AuthorName;//$this->testodaHTML($url,$AuthorName,"<font size=1>");
   $infoauthor.=$Quotation."|Affiliation=";
   $Affiliation=$this->testodaHTML($paginaHTML,$AuthorName,"<font size=-1>","</font></TD></TR>");
   $infoauthor.=$Affiliation."|Email address=To be completed}}";
   
   //GeneInfoBox
   $infogene="";
   $geneName=$this->testodaHTML($paginaHTML,"<HEAD>","<TITLE>","</TITLE>");
   $geneLocation=$this->testodaHTML($paginaHTML,"VALIGN=TOP >Location</TD>","TARGET=Bands><b>","</b>");
   $AtlasID=$this->testodaHTML($paginaHTML,"VALIGN=TOP >Atlas_Id</TD>","<TD><b>","</b>");
   $LocusID=$this->testodaHTML($paginaHTML,"LocusID (NCBI)","<b>","</b>");
   $infogene="{{GeneInfoBox
    |Name=$geneName
    |Location=$geneLocation
    |Atlas ID=$AtlasID
    |Locus ID=$LocusID
    |Link on Atlas=$url
    }}";
    //TODO: make an external link in the description of the image to open the image in a blank tab,
    //something like [[File::Name.ext|options|link(internal or external)]]
    
	//GenePage
    $topic="Description</TD><TD>";
    $stop="</TD></TR>";
    $Description=$this->textorimage($paginaHTML,'DNA/RNA',$topic,$stop);
    //$Description=$this->testodaHTML($paginaHTML,"DNA/RNA</FONT>","Description</TD><TD>","</TD></TR>");
    $Mutations=$this->testodaHTML($paginaHTML,"Mutations</FONT>","</TD><TD>","</TD></TR>");
    //Cambiamo ImplicatedIn: deve fare elenco * Malattia: Note
    //$ImplicatedIn=$this->testodaHTML($paginaHTML,"Implicated in</FONT>","Note</Strong></TD><TD>","</TD></TR>");
    $GenePage="{{GenePage
    |Description=$Description
    |Mutations=$Mutations
    |Implicated in=".$this->getImplicatedIn($paginaHTML)."
    }}[[Category:Gene]]
"; //implicated in;
$GLOBALS['NomeGene']=$geneName;
return $infoauthor.$infogene.$GenePage;
}

// This function creates a list of diseases in wich the gene or the protein is involved
public function getImplicatedIn($paginaHTML) {
	$ImplicatedIn="";
	$lastpos=strpos($paginaHTML,"Implicated in");
	$newrecord="";
	$nextstep=$lastpos;
	$ImplicatedThread=substr($paginaHTML, $lastpos);
	$finethread=strpos($ImplicatedThread,"<Strong><FONT SIZE=4>");
	$ImplicatedThread=substr($ImplicatedThread,0,$finethread);
	
	while(strpos($ImplicatedThread,"Entity")!==false) {
			if (strpos($ImplicatedThread,"Entity</Strong>")===false)
			{
				break;
			}//AGGIUSTA QUA, FORMAT E ESTRAZIONE DATI CORRETTA
			$ImplicatedThread=substr($ImplicatedThread, $nextstep);
			$entity=$this->testodaHTML($ImplicatedThread,"Entity</Strong>","</TD><TD><Strong>","</TD></TR>");
			$note=$this->testodaHTML($ImplicatedThread,"Entity</Strong>","Note</Strong></TD><TD>","</TD></TR>");
			$newrecord=$newrecord."* [[".strip_tags($entity)."]] - ".strip_tags($note)."\n";
			$nextstep=strpos($ImplicatedThread,$note)+strlen($note);

	}
	echo $newrecord."\n";
//}
	//forma da ottenere: * Malattia: Note
	return $newrecord;
}

// This function gets a list of the Gene pages that are actually on the Atlas.
public function GetPages() {
	$urlpages='http://atlasgeneticsoncology.org//Genes/';
	$elenco=$this->HtmlToString($urlpages);
	echo "Elenco:\n";
	$elenco=strip_tags($elenco);
	$elenco=str_replace(".html","",$elenco);
	$elenco=str_replace("Index of /Genes","",$elenco);
	$elenco=str_replace("Parent Directory","",$elenco);
	$elenco=trim(str_replace("png/","",$elenco));
	$array=explode("\n",$elenco);
	file_put_contents("elenco_html.txt",$elenco);
}
// This function retrieves the page content and edits the $section with the $newcontent
public function EditContent($page, $section, $newcontent) {
	$edittoken=$GLOBALS['edittoken']; //FUNZIONE DA FINIRE
	$wiki=$this->wiki;
	//prendi il contenuto della pagina
	$Pagetoedit=$this->get_page($page,$wiki);
	echo $Pagetoedit;
	//cerca la sezione
	//aggiorna i dati
}
}
?>
