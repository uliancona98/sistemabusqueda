<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRIW</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="images/magnifying-glass.svg" type="image/x-icon">
    <script
    src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    </head>
    <style>
/*.frmSearch {border: 1px solid #a8d4b1;background-color: #c6f7d0;margin: 2px 0px;padding:40px;border-radius:4px;}*/
#sugerencias-list{float:left;list-style:none;margin-top:-3px;padding:0;width:190px;position:static;}
#sugerencias-list li{padding: 10px; background: #FFFFFF; border-bottom: #ffffff 1px solid;}
#sugerencias-list li:hover{background:#ece3d2;cursor: pointer;}
/*#search-box{padding: 10px;border: #a8d4b1 1px solid;border-radius:4px;}*/
</style>
<body>
    <nav>
        <h1>
            Sistema de búsqueda y recuperación
        </h1>
    </nav>
    <!-- <script>console.log(JSON.parse('<?php// getResults()?>'));</script> -->
    <!-- <script>console.log(JSON.parse('<?php// getSugestions()?>'));</script> -->
    <?php 
        //calcularSuggestions();
    
    ?>
    
    <div style="margin-bottom: 30px;">
        <div class="searchingField">
            <img class="searchIcon" src="images/magnifying-glass.svg">
            <input type="search" autocomplete="off" name="search" placeholder="Búsqueda" id="inputSearch">
            <div id="suggesstion-box"></div>
            <button id="buscar" type="submit">Buscar</button>
        </div>
        <p style="font-size:20px">Para que tu busqueda sea exitosa tiene que empezar con un '(' y terminar
             con ')' y cada palabra <br> y caracter debe estar separada por espacio.</p>
        <p style="font-size:20px"> - (galletas AND calabaza)                - (galletas OR calabaza) NOT (donas NOT pera)<br>
            - (galletas papas OR calabaza) AND (donas OR pera)              - (carne leche)<br>
            Se acepta también la consulta de una frase sin parentesis ejemplo: <br> 
            *carne AND leche*                *carne leche huevo*                  *CARNE AND leche NOT huevo*
        </p>        
        <br>
        <div class="options">
            <!-- <label>opcion1</label> -->
        </div>
    </div>

    <div id="content" class="content">        
        <div id="loader"></div>
    </div>
</body>
<script type="text/javascript">
    function showRecords(perPageCount, pageNumber) {
        $.ajax({
            type: "GET",
            url: "buscar.php",
            data: "pageNumber=" + pageNumber,
            cache: false,
    		beforeSend: function() {
                $('#loader').html('<img src="loader.png" alt="reload" width="20" height="20" style="margin-top:10px;">');
    			
            },
            success: function(html) {
                $("#content").html(html);
                $('#loader').html(''); 
            }
        });
    }
    
    $(document).ready(function() {
        //showRecords(10, 1);

        $("#buscar").click(function(){
        //$("#mytable2").hide();
            var query = $("#inputSearch").val();
            console.log(query);
            $.ajax({
                type: "GET",
                url:'buscar.php',
                data: "query=" + query,
                cache: false,
                beforeSend: function() {
                    $('#loader').html('<img src="loader.png" alt="reload" width="20" height="20" style="margin-top:10px;">');
                    
                },
                success: function(html) {
                    $("#content").html(html);
                    $('#loader').html(''); 
                }
                });
        });



        $("#inputSearch").keyup(function(){
        console.log($(this).val());
		$.ajax({
		type: "GET",
		url: "buscar.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#inputSearch").css("background","#FFF url(LoaderIcon.gif) no-repeat 165px");
		},
		success: function(data){
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#inputSearch").css("background","#FFF");
		}
		});
	});
    });
    function selectSugest(val) {
        $("#inputSearch").val(val);
        $("#suggesstion-box").hide();
    }
    function aver(id){
    alert('event has been triggered'+id);
            $.ajax({
                type: "GET",
                url:'buscar.php',
                data: "id=" + id,
                cache: false,
                beforeSend: function() {
                    $('#loader').html('<img src="loader.png" alt="reload" width="20" height="20" style="margin-top:10px;">');
                    
                },
                success: function(html) {
                    $("#content").html(html);
                    $('#loader').html(''); 
                }
                });
   }
</script>
</html>
