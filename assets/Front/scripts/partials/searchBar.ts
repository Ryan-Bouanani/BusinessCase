
window.addEventListener('load', () => {

    // On récupère l'input de recherche de produit
    const inputSearchProduct = document.querySelector(".searchProduct") as HTMLInputElement;

    // On récupère la div qui va afficher les produits correspondants ou l'erreur
    const productResult = document.querySelector('.resultProductSearch') as HTMLDivElement;
    
    
    inputSearchProduct.addEventListener("keyup", function(){

        let searchValue = inputSearchProduct.value;

        // On envoie une requête Ajax vers le href du lien avec la méthode DELETE
        fetch('/filterSearch/' + JSON.stringify(searchValue), {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/json"
            },
        }).then(
            // On récupère la réponse en JSON
            response => response.json()
        ).then(data => {                         
            
            productResult.classList.remove('none');
            if(data.error) {
                // On affiche le résultat
                productResult.innerHTML = '<p class="errorSearchProduct">' + data['error'] + '</p>';
            } else{
                // Si pas d'érreur, on affiche les produits trouvés
                productResult.innerHTML = data;

                const products = [...document.querySelectorAll(".product")];         
            
                for(let product of products) {

                    // Au click sur un produit on cache la barre de resultat
                    product.addEventListener('click', () => {
                        productResult.classList.add('none');
                    })
                }

            }
        }).catch(e => alert(e))
    })
    
})


// TOGLE SEARCHBAR

// On récupere la loupe pour le toggle
const searchBar = document.querySelector('header .search-form');
// On récupere la div qui contient ma searchBar
const searchIcon = document.querySelector('header .search-menu-mobile .search-submit');

searchIcon.addEventListener('click', () => {
    searchBar.classList.toggle('none');
})
