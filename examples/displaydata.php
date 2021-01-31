<html>
<head>
    <script type="text/javascript" src="js/jquery-3.5.1.min.js"></script>
    <script type="text/javascript">

        /*var name=document.getElementById( "campoBusqueda" );
            
        if(name)
        {
            $.ajax({
                type: 'post',
                url: 'loaddata.php',
                data: {
                user_name:name,
                },
                success: function (response) {
                    // We get the element having id of display_info and put the response inside it
                    $( '#display_info' ).html(response);
                }
            });
        }
        else
        {
            $( '#display_info' ).html("Please Enter Some Words");
        }
    }*/
    $(document).ready(function() {
        var campoBusqueda=document.getElementById( "campoBusqueda" );

        $("#search").click(function() {                

            $.ajax({    //create an ajax request to display.php
                type: "POST",
                url: "loaddata.php",
                data: {
                search:campoBusqueda,
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

</head>
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
            <label for="queryf"></label>
            <input type="text" name="query" id="campoBusqueda">
             <input type="button" id="search" value="Buscar" />
        <!--<table border="1" align="center">
        <tr>
            <td> <input type="button" id="display" value="Buscar" /> </td>
        </tr>
        </table>-->
        <div id="responsecontainer" align="center">

        </div>


        <div id="display_info" >
        </div>
        
</body>
</html>