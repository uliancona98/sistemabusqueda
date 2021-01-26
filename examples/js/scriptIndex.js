
function generateTokens(){
    let txtArea = document.getElementById('txtArea');
    let val = txtArea.value;
    if(val == '') {
        alert('Escrba urls')
        return;
    }
    let split = val.split('\n');
    let formData = new FormData();
    for(let i = 0; i < split.length; i++) {
       formData.append('links['+i+']', split[i]);
    }
    //formData.append('links',val);
    let xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 ) {
            if (this.status == 200) {
                console.log(xmlhttp);
                let response = JSON.parse(xmlhttp.responseText);
                if (response.Errors != null){
                    document.getElementById('errors-msg').innerHTML = JSON.stringify(response.Errors);
                } else {
                    if(response) {
                        alert("Se recolectó información de las páginas web dadas");
                    } else {
                        alert("No se pudo recolectar información");
                    }
                    document.getElementById('errors-msg').innerHTML = '';
                }              
            }
            else {
                alert("Ocurrió un error al procesar la petición, la página no respondió con estatus 200")
            } 
        }
    };
    xmlhttp.open("POST", "./resources/Gestor.php");
    xmlhttp.send(formData);
    
}

function restore() {
    let alerta = "¿Seguro desea reestablecer el crawler, esto borrará los enlaces guardados y la información de la BD?";
    if(confirm(alerta)) {
        let dom = document.getElementById('txtArea');
        dom.value = "";
        let formData = new FormData();
        formData.append('delete', true);
        let xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 ) {
                if (this.status == 200) {
                    let response = JSON.parse(xmlhttp.responseText);
                    if(response) {
                        alert("Se eliminó toda la información");
                    } else {
                        alert("No se pudo eliminar la información");
                    }      
                }
                else {
                    alert("Ocurrió un error al procesar la petición, la página no respondió con estatus 200")
                } 
            }
        };
        xmlhttp.open("POST", "./resources/Gestor.php");
        xmlhttp.send(formData);
    }
}
