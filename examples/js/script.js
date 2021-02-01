
let timeout = null;
const input = document.querySelector('input[type="search"]');
input.addEventListener('input', updateInputValue);
input.addEventListener('blur', ()=>{ setTimeout (deleteOptions, 150); });

const button = document.querySelector('button[type="submit"]');
button.addEventListener('click', (evt)=>{searchQuery(input.value.trimStart())});

function updateInputValue(evt) {
    clearTimeout(timeout);
    timeout = setTimeout(function () {
        getOptions(evt.target.value.trimStart());
    }, 500);
}

function getOptions(strValue) {
    // console.log(strValue == null || strValue.length == 0, 'getOptFunc');
    deleteOptions();
    if (strValue || strValue.length > 0) {
        let xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function () {

            if (this.readyState == 4 && this.status == 200) {
                // console.log(this.responseText);
                let options = JSON.parse(this.responseText);
                if (options.length > 0) {
                    showOptions(options);
                    document.querySelector('.searchingField').classList.add('hasOptions');
                }
                
            }
        };
        let phpFile = 'operations.php';
        let variable = 'query';
        xmlHttp.open('GET', `${phpFile}?${variable}=${strValue}`, true);
        xmlHttp.send();
    }
}

function showOptions(options) {
    const optDiv = document.querySelector('.options');
    options.forEach(element => {
        let lbl = document.createElement('label');
        lbl.textContent = element;
        optDiv.appendChild(lbl);
    });

    selectOptionValue();
}

function selectOptionValue() {
    const lblOptions = document.querySelectorAll('.options>label');
    lblOptions.forEach((lbl) => {
        lbl.addEventListener('click', () => {
            input.value = lbl.textContent;
            input.dispatchEvent(new Event('input'));
        });
    });
}


function deleteOptions() {
    const lblOptions = document.querySelectorAll('.options>label');

    lblOptions.forEach((lbl) => {
        lbl.remove();
    });
    document.querySelector('.searchingField').classList.remove('hasOptions');
}