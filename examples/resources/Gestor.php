<?php
include('crawler.php');
$directory = '../documents/';
$errors = array();
$newDocument = true;
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crawler";
session_start();


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if(isset($_POST['links'])) {
        $links = $_POST['links'];
        $archivo = fopen('../links.txt', 'w');
        foreach($links as $link) {
            if(!empty($link)) {
                fwrite($archivo, $link.PHP_EOL);
            }
        }
        fflush($archivo);
        fclose($archivo);
        
        //removeRecordsFromDB();
        foreach($links as $targetWithTags) {
            if(!empty($targetWithTags)) {
                $arrData = explode(" ", $targetWithTags);
                $target = $arrData[0];
                $tag = array();
                if(count($arrData)>1) {
                    $tag = explode(":", $arrData[1]);
                }
                $documentId = getDocumentID($target);
                try {
                    $arr = generate($target, getLastModifiedIfExists($target), $documentId == 0 ? false: true);
                    if(!empty($arr)) {
                        if(!isset($arr['Error'])) {
                            $arr['Tags'] = $tag;
                            if($documentId == 0) {
                                saveDataRecord($arr, $target);
                            } else {
                                updateDataRecord($arr, $documentId);
                            }
                        } else {
                            $errors[] = array (
                                'link' => $url,
                                'Error' => $arr['Error']
                            );
                        }
                    }
                } catch(Exception $ex) {
                    $errors[] = array (
                        'link' => $ex->getMessage()
                    );
                }
                
                $urls = getUrls($target, 10);
                foreach($urls as $url) {
                    $dId = getDocumentID($url);
                    try {
                        $str = generate($url, getLastModifiedIfExists($url), $dId == 0 ? false: true);
                        if(!empty($str)) {
                            if(!isset($str['Error'])) {
                                $str['Tags'] = $tag;
                                if($dId == 0) {
                                    saveDataRecord($str, $url);
                                } else {
                                    updateDataRecord($str, $dId);
                                }
                            } else {
                                $errors[] = array (
                                    'link' => $url,
                                    'Error' => $str['Error']
                                );
                            }
                        }
                    } catch(Exception $ex) {
                        $errors[] = array (
                            'link' => $ex->getMessage()
                        );
                    }
                }
            }
        }
        if (empty($errors)) {
            echo json_encode(true);
        } else {
            echo json_encode(array(
                'Errors' => $errors
            ));
        }
    } else if (isset($_POST['delete'])) {
        $archivo = fopen('../links.txt', 'w');
        fclose($archivo);
        echo json_encode(removeRecordsFromDB());
    }
} else {
    //$pages = array("http://localhost/BRIW/crawler/test/test.html", "https://en.wikipedia.org/wiki/Coronavirus_disease_2019");//, "http://www.schrenk.com/nostarch/webbots/page_with_broken_links.php", "https://developer.mozilla.org/es/docs/Web/HTML", "https://es.wikipedia.org/wiki/HTML");
    //var_dump($pages);
    //foreach($pages as $targetWithTags) {
    //}
}

function saveDataRecord($arrData, $link) {
    global $errors;
    $conn = getConnection();
    if(!$conn) {
        return null;
    }
    $sql = "INSERT INTO documents (link, title, fullDocumentText, lastTimeVisited) VALUES ('$link', '{$arrData['Title']}', '{$arrData['Content']}', '{$arrData['LastModified']}')";
    $bol = $conn->query($sql);
    if(!$bol) {
        $errors['Error'] = "Ocurrió un error al intentar guardar los datos del archivo en la base de datos";
    }else{
        var_dump($arrData['Tags']);

        $last_id = $conn->insert_id;
        var_dump($arrData['Tags']);

       $_SESSION['id_page']   =$last_id;
        $_SESSION['link']  = $link;
        $_SESSION['title']  = $arrData['Title'];
        $_SESSION['content']  = $arrData['Content'];
        $_SESSION['category']  =$arrData['Tags'];
        header("Location: ./../update.php");
    }
    $conn->close();
    return $bol;
}

function getLastModifiedIfExists($link) {
    global $errors;
    $conn = getConnection();
    if(!$conn) {
        return null;
    }
    $sql = 'SELECT lastTimeVisited FROM documents WHERE link = "'.$link.'" LIMIT 1';
    $result = $conn->query($sql);
    if($result) {
        $row = $result->fetch_assoc();
        $conn->close();
        if(!empty($row)) {
            return $row['lastTimeVisited'];
        } else {
            return '01-01-1970 01:00:00';
        }
    } else {
        return '01-01-1970 01:00:00';
    }
}

function updateDataRecord($arrData, $id) {
    global $errors;
    $conn = getConnection();
    if(!$conn) {
        return null;
    }
    $sql = "UPDATE documents SET title = '{$arrData['Title']}', fullDocumentText = '{$arrData['Content']}', lastTimeVisited = '{$arrData['LastModified']}' WHERE documentId = '$id'";
    $bol = $conn->query($sql);
    if(!$bol) {
        $errors['Error'] = "Ocurrió un error al intentar guardar los datos del archivo en la base de datos";
    }
    $conn->close();
    return $bol;
}

function removeRecordsFromDB() {
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $errors['Error'] = "Ocurrió un error al intentar conectar a la base de datos. " . "Connection failed: " . $conn->connect_error;
        return false;
    }
    $sql = "DELETE FROM documents;";
    $bol = $conn->query($sql);
    if(!$bol) {
        $errors['Error'] = "Ocurrió un error hacer reset a la bd";
    }
    $conn->close();
    return $bol;
}

function getDocumentID($link) { //los nombres de los documentos serán únicos
    global $errors;
    $conn = getConnection();
    if(!$conn) {
        return null;
    }
    $sql = "SELECT documentID FROM documents WHERE link = '$link'";
    $result = $conn->query($sql);
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['documentID'];
    }else{
        //$errors['Error'] = "Ocurrió un error";
        $conn->close();
        return 0;
    }
}

function getConnection() {
    global $servername, $username, $password, $dbname, $errors;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $errors['Error'] = "Ocurrió un error al intentar conectar a la base de datos. " . "Connection failed: " . $conn->connect_error;
        return null;
    }
    return $conn;
}