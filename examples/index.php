<?php
?>
<!DOCTYPE html>
<html lang="es-MX">
    <head>
        <title>WebBot</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="css/stylesIndex.css">
    </head>
    <body>
        <div class="container">
            <header class="flex-container">
                <h2>Crawler</h2>
            </header>
            <article>
                <section class="search-form">
                    <form action="">
                        <div id="documents-section" style="width: 800px;">
                            <div class="field" style="width: 800px;">
                                <label for="file-input1">Páginas a visitar:</label>
                                <!--<input id="file-input1" type="file" accept=".doc,.docx,.pdf,.txt" style="width: 300px;" required>-->
                                <textarea id='txtArea' rows="8" cols="100" autofocus><?php
                                        if(file_exists('links.txt')) {
                                            $fp = fopen('links.txt', 'r');
                                            while (!feof($fp)) {
                                                $linea = fgets($fp);
                                                echo $linea;
                                            }
                                            fclose($fp);
                                        }
                                    ?></textarea>
                            </div>
                        </div>
                        <div class="buttons-section" style="align-self: center;">
                            <button class="search-button" type="button" onclick="generateTokens()">Obtener información</button>
                            <button class="search-button" type="button" onclick="restore()">Reestablecer</button>
                        </div>
                    </form>
                </section>
                <section class="errors">
                    <p id='errors-msg'></p>
                </section>
                
            </article>
        </div>
    </body>
    <script src="js/scriptIndex.js"></script>
</html>