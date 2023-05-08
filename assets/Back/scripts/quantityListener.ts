import { deleteContentShoppingCart } from "./fetch/deleteContentShoppingCart";

// Add Dynamic Quantity products order back office

// J'initialise mes variables qui vont contenir différents elements du DOM
let quantityArray: NodeListOf<HTMLInputElement>;
let addQuantity: NodeListOf<HTMLInputElement>;
let subtractQuantity: NodeListOf<HTMLInputElement>;

let priceProduct: NodeListOf<Element>;
let priceTotalLine:  NodeListOf<Element>;
let priceTotalOrder: Element;
let arrayBtnQuantity;
export let btnsDelete: NodeListOf<HTMLButtonElement>;

let arrayPriceOrder: Array<Number> = []; 

updateNodeListInputBtnQuantity();

/**
 * Cette fonction va permettre de gérer l'increment et le decrement de produits ainsi qu'afficher le prix de chaque ligne du panier et le total de la commande
 */
export function updateNodeListInputBtnQuantity(): void {

    // inputs de quantité
    quantityArray = document.querySelectorAll(' .lineQuantity .quantity');  
    // btn d'ajout de quantité
    addQuantity = document.querySelectorAll(' .lineQuantity button.increment');
    // btn de soustraction de quantité
    subtractQuantity = document.querySelectorAll(' .lineQuantity button.decrement');
    priceProduct =  document.querySelectorAll(' .priceProduct');
    priceTotalLine = document.querySelectorAll(' .priceTotalLine');
    priceTotalOrder = document.querySelector(' .priceTotalOrder');
    btnsDelete = document.querySelectorAll("[data-delete]");

    if (quantityArray.length !== 0) {
        
        arrayBtnQuantity = [addQuantity, subtractQuantity];
    
        // Je lace la function qui va me permettre de supprimer un produit d'une commande
        deleteContentShoppingCart(btnsDelete);
    
        // Je récupère la value de chaque inputs de quantité et les stockent dans un tableau
        quantityArray.forEach((quantity : HTMLInputElement, key) => {
    
            let quantityValue = parseInt(quantity.value);
            updateBtnQuantity(quantityValue, key);
    
            // Met à jour les buttons et la valeur de l'input en fonction de la quantité entrer par l'utilisateur
            inputQuantityListener(quantity, key);
        });
    
        // Pour chacun des tableaux de buttons (increment et decrement) je lance la fonction quantityListener()
        arrayBtnQuantity.forEach((element: NodeListOf<HTMLButtonElement>) => {
            quantityListener(element);
        }); 
    }
}



// Function qui met à jour la quantité selon le button cliqué
function quantityListener(arrayBtnQuantity: NodeListOf<HTMLButtonElement>) {

    // Pour chaque click sur un button + ou - j'ajoute ou soustrait la value de l'input en question
    arrayBtnQuantity.forEach((element, key) => {
        element.addEventListener('click', (e: Event) => {
            // Je désactive le comportement par default de mes buttons
            e.preventDefault();

            let quantityValue = parseInt(quantityArray[key].value);
                
                // Si click sur un button d'ajout, on incrémente
                if (element === addQuantity[key]) {      
                    // on incrémente
                    quantityValue++;                       
                } else {
                    // Si click sur un button de soustraction, on décrémente
                    quantityValue--;
                } 
                // Je met à jour les buttons de quantités
                updateBtnQuantity(quantityValue, key);
        
        }); 
    });
}

// Met à jour la valeur de l'input en fonction de la quantité entrer par l'utilisateur
function inputQuantityListener(quantity: HTMLInputElement, key: number) {
    quantity.addEventListener("keypress", function(e) {
        
        // Si l'utilisateur appuie sur ENTRER et que la value de l'input n'est pas vide
        if (e.key === "Enter") {
            e.preventDefault();
            
            let quantityValue = parseFloat(quantity.value);
            
            if (quantity.value !== '') {
                
                // On verifie la quantité entrer
                if (isInt(quantityValue)) {   
    
                    if (quantityValue > 20) {
    
                        quantityValue = 20;
    
                    } else if (quantityValue < 1) {
    
                        quantityValue = 0;
    
                    }
                } else {          
                    quantityValue = 0;    
                }
            } else {          
                quantityValue = 0;  
            }                
            // Je met à jour les buttons de quantités
            updateBtnQuantity(quantityValue, key);   
        }
    })
}

export function removePriceTotalLineDelete(key: number) {

    arrayPriceOrder.splice(key, 1);

    // On met à jour le prix de la commande
    resultTotalOrder(arrayPriceOrder);
}


/**
 * Cette function va permettre d'afficher le prix total de la ligne d'un panier
 * @param key 
 * @returns Void
 */
function totalPriceLineListener(key = null) {

    if (key !== null) {
        
        // Je multiplie la prix TTC par la quantité
        let result = resultPriceTotalLine(key);

        // J'affiche le prix de la ligne du panier
        priceTotalLine[key].textContent = result + '€'; 
        
        // Je met à jour le prix de la ligne dans le tableau
        if (key !== priceTotalLine.length - 1) { 
            arrayPriceOrder[key] = parseFloat(result);
        }
    } else {    
        priceTotalLine.forEach((priceTotal, key) => {
            
            // Je multiplie la prix TTC par la quantité
            let result = resultPriceTotalLine(key);

            // J'affiche le prix de la ligne du panier
            priceTotal.textContent = result + '€';
            
            // Tant que ce n'est pas le prix total de l'ajout d'un produit
            if (key !== priceTotalLine.length - 1) {                           
              arrayPriceOrder.push(parseFloat(result));
            }
        });
    }
    // On affiche le prix de la commande
    resultTotalOrder(arrayPriceOrder);
}


/**
 * Cette fonction va permettre d'afficher le prix total de la commande
 * @param arrayPriceOrder 
 */
function resultTotalOrder(arrayPriceOrder: Array<Number>) {
  
    let priceOrder: number = 0;

    arrayPriceOrder.forEach((price: number) => {
        priceOrder += price;
    });
    priceTotalOrder.innerHTML = (priceOrder.toFixed(2)).toString() + '€';
    arrayPriceOrder = [];
}


/**
 * Cette fonction va permettre de multiplier le prix TTC par la quantité
 * @param key 
 * @returns string
 */
function resultPriceTotalLine(key: number): string {
     return (parseFloat(quantityArray[key].value) * parseFloat(priceProduct[key].innerHTML)).toFixed(2);
}


/**
 * Cette fonction va permettre de mettre à jour le comportement des buttons : "+" et "-" (disabled ou enabled) et mettre à jour l'input de quantité du produit
 * @param quantity 
 * @param key 
 * @return Void
 */
function updateBtnQuantity(quantity, key: number): void {
    quantity = parseInt(quantity);

    // Si supérieur à 9 alors je désactive le button ajouter
    if (quantity >= 20) {
        addQuantity[key].setAttribute('disabled', '');
    } 
    if (quantity > 0) {
        // Si supérieur à 0 alors je réactive le button soustraction
        subtractQuantity[key].removeAttribute('disabled');
    }
    if (quantity <= 0) {
        // Si inférieur ou égale à 1 alors je désactive le button soustraction
        subtractQuantity[key].setAttribute('disabled', '');
    } 
    if (quantity < 20) {
        // Si inferieur à 10 alors je réactive le button ajouter
        addQuantity[key].removeAttribute('disabled');
    }
    // Je met à jour la value de mon input et le prix total de la ligne
    quantityArray[key].value = quantity.toString();

    // On met à jour les prix de la ligne et celui de la commande 
    totalPriceLineListener(key);
}


/**
 * Cette fonction va permettre de vérifier si la valeur est un entier ou non
 * @param value 
 * @returns Boolean
 */
function isInt(value) {
    if (
      typeof value === 'number' &&
      !Number.isNaN(value) &&
      Number.isInteger(value)
    ) {
      return true;
    }
    return false;
  }