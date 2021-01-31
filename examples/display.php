<html>
 <script type="text/javascript" src="js/jquery-3.5.1.min.js"> </script>

 <script type="text/javascript">

 $(document).ready(function() {
    var campoBusqueda=document.getElementById( "campoBusqueda" );

    $("#search").click(function() {                

      $.ajax({    //create an ajax request to display.php
        type: "GET",
        url: "loaddata.php",  
        data: {
            query=campoBusqueda,
        },   
        dataType: "html",   //expect html to be returned                
        success: function(response){                    
            $("#responsecontainer").html(response); 
            //alert(response);
        }

    });
});
});

</script>

<body>
    <h3 align="center">Manage Student Details</h3>
    <h1>Ingresa una busqueda</h1>
    <p>Para que tu busqueda sea exitosa tiene que empezar con un '(' y terminar
            con ')' y cada palabra y caracter debe estar separada por espacio.</p>
    <p>Ejemplo 1: (galletas AND calabaza)<br>
        Ejemplo 2: (galletas OR calabaza) NOT (donas NOT pera)<br>
        Ejemplo 3: (galletas papas OR calabaza) AND (donas OR pera)<br>
        Ejemplo 4: (carne leche)<br>
    </p>
    <form action="" method="GET">
        <input type="text" name="campoBusqueda" id="campoBusqueda">
        <input type="button" id="search" value="Buscar" />
    </form>
    <!--<table border="1" align="center">
    <tr>
        <td> <input type="button" id="display" value="Buscar" /> </td>
    </tr>
    </table>-->
    <div id="responsecontainer" align="center">

    </div>
</body>
</html>