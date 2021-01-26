<?php
include('crawler.php');
$directory = '../documents/';
$errors = array();
$newDocument = true;
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crawler";


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
        foreach($links as $target) {
            if(!empty($target)) {
                $documentId = getDocumentID($target);
                $arr = generate($target, getLastModifiedIfExists($target));
                if(!empty($arr)) {
                    if(!isset($str['Error'])) {
                        if($documentId == 0) {
                            saveDataRecord($arr, $target);
                        } else {
                            updateDataRecord($arr, $documentId);
                        }
                    } else {
                        $errors[] = array (
                            'link' => $str['Error']
                        );
                    }
                }
                
                $urls = getUrls($target);
                foreach($urls as $url) {
                    $dId = getDocumentID($url);
                    $str = generate($url, getLastModifiedIfExists($url));
                    if(!empty($str)) {
                        if(!isset($str['Error'])) {
                            if($dId == 0) {
                                saveDataRecord($str, $url);
                            } else {
                                updateDataRecord($str, $dId);
                            }
                        } else {
                            $errors[] = array (
                                'link' => $str['Error']
                            );
                        }
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
    //$pages = array("http://localhost/BRIW/crawler/test/test.html");//, "http://www.schrenk.com/nostarch/webbots/page_with_broken_links.php", "https://developer.mozilla.org/es/docs/Web/HTML", "https://es.wikipedia.org/wiki/HTML");
    //foreach($pages as $target) {
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
        $last_id = $conn->insert_id;

        session_start();
        $_SESSION['id']   =$last_id;
        $_SESSION['link']  = $link;
        $_SESSION['title']  = $arrData['Title'];
        $_SESSION['content']  = $arrData['Content'];
        $_SESSION['category']  = "Página Web";
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
