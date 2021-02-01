<?php 
require_once 'operations.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRIW</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="images/magnifying-glass.svg" type="image/x-icon">
</head>

<body>
    <nav>
        <h1>
            Sistema de búsqueda y recuperación
        </h1>
    </nav>
    <!-- <script>console.log(JSON.parse('<?php// getResults()?>'));</script> -->
    <!-- <script>console.log(JSON.parse('<?php// getSugestions()?>'));</script> -->

    <div style="margin-bottom: 30px;">
        <div class="searchingField">
            <img class="searchIcon" src="images/magnifying-glass.svg">
            <!-- onkeyup="search(this)" -->
            <input type="search" autocomplete="off" name="search" placeholder="Búsqueda" id="">
            <button type="submit">Buscar</button>
        </div>

        <div class="options">
            <!-- <label>opcion1</label> -->
        </div>
    </div>

    <div class="content">
        <div class="side-left">
            <div class="categories">
                <h3 class="categoriesTitle">Categorías</h3>
                <div class="categoryItems">
                    <!-- <label class="category">opcion3</label>
                    <label class="category">opcion3</label>
                    <label class="category">opcion3</label> -->

                </div>
            </div>

        </div>
        <div class="side-right">
            <div class="corrections">
                <h3 class="correctionsTitle">Correcciones</h3>
                <div class="correctionItems">
                    <!-- <label class="correction">opcion3</label>
                    <label class="correction">opcion3</label>
                    <label class="correction">opcion3</label> -->
                </div>

            </div>

        </div>
        <div class="centre">
            <div class="results">
                <h3>Resultados:</h3>
                <div class="resultSection">
                   <!-- <div class="resultOption">
                        <p><b>Título: </b> sdf </p>
                        <p>Categorías: </p>
                        <p>URL: </p>
                        <p>Snippet: </p>
                    </div> -->
 <!-- 
                    <div class="resultOption">
                        <p>Título: </p>
                        <p>Categorías: </p>
                        <p>URL: </p>
                        <p>Snippet: </p>
                    </div>

                    <div class="resultOption">
                        <p>Título: </p>
                        <p>Categorías: </p>
                        <p>URL: </p>
                        <p>Snippet: </p>
                    </div> -->
                </div>
                

            </div>
        </div>

    </div>
    <script src="js/search.js"></script>
    <script src="js/script.js"></script>
</body>

</html>