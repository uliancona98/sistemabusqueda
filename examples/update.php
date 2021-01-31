<?php

require_once(__DIR__.'/init.php');
//htmlHeader();
//include('index.php');
session_start();
    // if data is posted add it to Solr
    // create a client instance
    $client = new Solarium\Client($adapter, $eventDispatcher, $config);

    // get an update query instance
    $update = $client->createUpdate();
    // create a new document for the data
    // please note that any type of validation is missing in this example to keep it simple!
    $doc = $update->createDocument();

    $doc->id =  $_SESSION['id_page'];
    $doc->url =$_SESSION['link'] ;
    $doc->title = $_SESSION['title'];

    foreach($_SESSION["category"] as $key => $value){
        $doc->addField('category', (string)  $value);
    }

    $doc->content = $_SESSION['content'];
    // add the document and a commit command to the update query
    $update->addDocument($doc);
    $update->addCommit();

    // this executes the query and returns the result
    $result = $client->update($update);

    echo '<b>Update query executed</b><br/>';
    echo 'Query status: ' . $result->getStatus(). '<br/>';
    echo 'Query time: ' . $result->getQueryTime();
    //
    
    //session_unset();

   //session_destroy();

    // if no data is posted show a form
//htmlFooter();