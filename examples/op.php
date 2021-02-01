<?php
if(isset($_GET['query'])) {
    echo getSugestions();
    return;
} 
if(isset($_GET['search'])){
    // echo getCorrections();
    // echo getCategories();
    $res = getResults();
    $cat = getCategories();
    $cor = getCorrections();
    echo json_encode(['results' => $res, 'categories'=>$cat, 'corrections'=>$cor]);
    // return;
}


function getResults() {
   $results = [
       [
           'id'=>1, 
           'title'=>"Canzando ls...", 
           'url'=> 'http:..',
           'category'=> ['cat2', 'cat1'],
           'snippet' => 'cazando gangas mex',
           'score' => 1.86972342,
       ],
       [
           'id'=>2, 
           'title'=>"terk9sdpo ls...", 
           'url'=> 'http:..',
           'category'=> ['cat4', 'cat1'],
           'snippet' => 'cazando gangas mex',
           'score' => 1.36972342,
       ],
       [
           'id'=>4, 
           'title'=>"como alsdd ls...", 
           'url'=> 'http:..',
           'category'=> ['cat8', 'cat1'],
           'snippet' => 'termina gangas mex',
           'score' => 1.16972342,
       ],
        ];
    return json_encode($results); 
}

function getCategories() {
    $categories = [['cat2', 2], ['cat4', 2], ['cot', 0], ['catt', 0]];
    return json_encode($categories);
}

function getCorrections() {
    $corrections = ['alex', 'carlos', 'andrea'];
    return json_encode($corrections);
}

function getSugestions() {
    $sugestions = ['a  lex', 'carlos', 'andrea', '1234', '234', '345'];
    $arr = array_values(array_filter($sugestions, 'filter'));
    return json_encode($arr);
}

function filter($val) {
    
    $query = $_GET['query'];
    $len = strlen($query); 
    return (substr($val, 0, $len) === $query); 
}

// $results = (Object)[
//         (object)['fields'=>[
//             'id'=> 79,
//             'url' => ['www.https...'],
//             'title' => 'mejora vir',
//             'category' => ['Cat3'],
//             'content' => ['contenido'],
//             '_version_' => 1690187199826362368,
//             'score' => 0.84473014
//         ]
//     ],
//         (object)['fields'=>[
//             'id'=> 79,
//             'url' => ['www.https...'],
//             'title' => 'mejora vir',
//             'category' => ['Cat3'],
//             'content' => ['contenido'],
//             '_version_' => 1690187199826362368,
//             'score' => 0.84473014
//         ]
//     ],
//         // (object)[1, 23,4],
//         // new ParentClass(),
//     ];
?>