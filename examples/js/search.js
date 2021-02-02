// console.log('hola mundo script2');

function searchQuery(strValue) {
    // console.log(strValue);
    if (strValue == null || strValue.length == 0) {
        return;
    }
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function () {

        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            let data = JSON.parse(this.responseText);
            console.log(data);
            let dataResult = JSON.parse(data.results);
            let dataCategories = JSON.parse(data.categories);
            let dataCorrections = JSON.parse(data.corrections);
            console.log(dataResult, dataCategories, dataCorrections);

            deleteDOM();
            showResults(dataResult); //work
            showCategories(dataCategories); //work
            showCorrections(dataCorrections); //work

        }
    };
    let phpFile = 'op.php';
    let variable = 'search';
    xmlHttp.open('GET', `${phpFile}?${variable}=${strValue}`, true);
    xmlHttp.send();
}

function showCategories(corrections) {
    let divCat = document.querySelector('.categoryItems');
    corrections.forEach(element => {
        let lbl = document.createElement('label');
        lbl.textContent = `${element[0]} (${element[1]})`;
        divCat.appendChild(lbl);
    });

}
function showCorrections(corrections) {
    let divCor = document.querySelector('.correctionItems');
    corrections.forEach(element => {
        let lbl = document.createElement('label');
        lbl.textContent = element;
        divCor.appendChild(lbl);
    });
}

function showResults(results) {
    let divResult = document.querySelector('.resultSection');
    results.forEach((element) => {
        let div = document.createElement('div');
        div.setAttribute('class', 'resultOption');

        let pTitle = document.createElement('p');
        let tit = document.createElement('b');
        tit.textContent = 'Título: ';
        pTitle.appendChild(tit);
        pTitle.appendChild(document.createTextNode(element.title));
        div.appendChild(pTitle);

        let pCat = document.createElement('p');
        let cat = document.createElement('b');
        cat.textContent = 'Categorías: ';
        pCat.appendChild(cat);
        pCat.appendChild(document.createTextNode(element.category.join(', ')));
        div.appendChild(pCat);

        let pUrl = document.createElement('p');
        let url = document.createElement('b');
        url.textContent = 'URL: ';
        pUrl.appendChild(url);
        pUrl.appendChild(document.createTextNode(element.url));
        div.appendChild(pUrl);

        let pSnippet = document.createElement('p');
        let snip = document.createElement('b');
        snip.textContent = 'Snippet: ';
        pSnippet.appendChild(snip);
        pSnippet.appendChild(document.createTextNode(element.snippet));
        div.appendChild(pSnippet);

        divResult.appendChild(div);
    });
}

function deleteDOM() {
    let results = document.querySelectorAll('.resultSection>div');
    results.forEach((element) => {
        element.remove();
    });

    let divCor = document.querySelectorAll('.correctionItems>label');
    divCor.forEach((element) => {
        element.remove();
    });

    let divCat = document.querySelectorAll('.categoryItems>label');
    divCat.forEach((element) => {
        element.remove();
    });
}