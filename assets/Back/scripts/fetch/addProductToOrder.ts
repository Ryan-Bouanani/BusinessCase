import { updateNodeListInputBtnQuantity } from "../quantityListener";
window.addEventListener('load', () => {
    
    // On récupère le button d'ajout de produit
    const btnAddProduct = document.querySelector(".newProduct .btnAddProduct");

    // On récupere la div qui va afficher les produits correspondants
    const productResult = document.querySelector('.resultProductSearch');

    // On récupère l'input d'ajout de produit
    const inputProduct = document.querySelector(".addProduct") as HTMLInputElement;
    
    // On récupère l'input de quantité du nouveau de produit
    const quantityProduct = document.querySelector(' .newProductInfo .lineQuantity .quantity') as HTMLInputElement;
   
    // On récupère l'input du prix et total du nouveau produit
    const productPrice = document.querySelector(".newProductInfo .priceProduct");  
    const displayTotalNewProductPrice = document.querySelector(".newProductInfo .priceTotalLine");
    
    const ProductData = [inputProduct, productPrice, quantityProduct, displayTotalNewProductPrice];  
    
    let productTitle = undefined as string;
    
    // On écoute au keyup
    if (inputProduct) {
        inputProduct.addEventListener("keyup", function(){
        
            let searchValue = inputProduct.value;
        
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
                    // On affiche le resultat
                    productResult.innerHTML = '<p class="errorSearchProduct">' + data['error'] + '</p>';
                } else{
                    // Si pas d'érreur, on affiche les produits trouvés
                    productResult.innerHTML = data;
        
                    const products = [...document.querySelectorAll(".product")];         
                
                    for(let product of products) {
        
                        // Au click sur un produit on ajoute son nom en tant que value dans l'input d'ajout de produit et son prix dans un <p>
                        product.addEventListener('click', () => {
                            const productsTitle = product.querySelector(".productTitle");         
                            const productsPrice = product.querySelector(".priceProduct");
                            // const productsId = product.querySelector(".productId");              
                            
                            displayOrResetDataNewProduct(productsTitle.textContent, productsPrice.textContent);
                            
                            // On stock la nom du futur produit ajouté 
                            productTitle = productsTitle.textContent;
                            // productId = productsTitle.textContent;
                            
                            productResult.classList.add('none');
                        })
                    }
        
                }
            }).catch(e => alert(e))
        })
    }
        
    

    // Au click sur le bouton d'ajoute de produit
    if (btnAddProduct) {
        btnAddProduct.addEventListener('click', function(e) {
            // On empêche la navigation
            e.preventDefault()
                
            const error = document.querySelector('.error');

            // Si un produit est entrer alors on requette la route qui va ajouter une ligne à notre panier
            if (typeof productTitle !== 'undefined' && inputProduct.value === productTitle ) {    

                fetch(this.getAttribute("href") + "/" + inputProduct.value + "/" + quantityProduct.value , {
                    method: "POST",
                    headers: {
                        "X-Requested-With":
                        "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                }).then(
                        // On récupère la réponse en JSON
                        response => response.json()
                ).then( data => {
                    displayOrResetDataNewProduct('', '0.00€');               
                    if(data.error) {
                        // On affiche un erreur
                        const error = document.querySelector('.error');

                        error.classList.remove('none');
                        error.textContent =  data['error'];

                    } else {
                        let tbody = document.querySelector('.contentShoppingCart tbody');
                        error.classList.add('none');

                        tbody.innerHTML = data;
                        updateNodeListInputBtnQuantity(); 
                        console.log(inputProduct);
                                    
                    }
                }).catch(e => alert(e))
            } else {
                error.classList.remove('none');
                error.textContent = 'Veuillez d\'abord selectionner un produit';
            }
        })
    }

    // Display or reset data new product
    function displayOrResetDataNewProduct(productsTitle: string, productsPrice: string) {
        // On ajoute le titre du produit et ses prix a nos element html pour l'affichage                     
        ProductData.forEach(element => {            
            if (element == inputProduct) {
                inputProduct.value = productsTitle;
            } else if (element == quantityProduct) {
                quantityProduct.value = '1';
                quantityProduct.click();
            } else {
            element.innerHTML = productsPrice; 
            }            
        });
    }
});

