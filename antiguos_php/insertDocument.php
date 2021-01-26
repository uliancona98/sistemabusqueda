<?php
//comando: composer require minhd/solr-client
    require_once 'vendor/autoload.php';
    use MinhD\SolrClient\SolrClient;
    use MinhD\SolrClient\SolrDocument;

    $client = new SolrClient('localhost', '8983');
    $client->setCore('busquedarecup');

// Adding document
$client->add(
    new SolrDocument([
        'title' => 'Title',
        'url' => 'php.com',
        'text' => 'El texto de la pagina'

    ])
);
$client->commit();

// Searching document
$result = $client->query('text:pagina');
echo $result->getNumFound(); // 1
foreach ($result->getDocs() as $doc) {
    echo '<pre>';
        print_r($doc);
    echo  '</pre>';
}

?>