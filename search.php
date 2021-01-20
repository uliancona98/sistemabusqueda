<?php
if(isset($_GET["page"])){
	$page = (int)$_GET["page"];
}
else {
	$page = 1;
}

$query = urldecode($_GET["query"]);
$query = str_replace(" ","%20",$query);

$core_url = "http://localhost:8983/solr/films/select?q=";
$start=$page*10-10;

$contents = file_get_contents($core_url.$query.'&wt=php&rows=10&start='.$start.'');
eval("\$result = " . $contents . ";");
$count = $result["response"]["numFound"];
$numOfPages = ceil($count/10);

if($count==0){
	echo "no results found";
}

for($i=0; $i<sizeof($result["response"]["docs"]) ; $i++){
	echo "=====Result[".($i+1+$start)."]======<br/>";
	foreach($result["response"]["docs"][$i] as $key=>$value){
		display($key,$value);
	}
	echo "<br/>";
}

for($i=0; $i< $numOfPages ; $i++){
	echo "<a href='search.php?page=".($i+1)."&query=".$query."'>[".($i+1)."]</a> ";
}


function display($k,$x){
	if(!isset($x)){
		return;
	}
	
	echo $k.": ";
	
	if(!is_array($x)){
		echo $x;
		echo "<br>";
	}
	else {
		for($i=0; $i<sizeof($x) ; $i++){
			if(sizeof($x)==1 || $i==sizeof($x)-1){
				echo $x[$i];
			}
			else {
				echo $x[$i].' - ';
			}
		}
		echo "<br>";
	}
}

?>