
import { removePriceTotalLineDelete } from "../quantityListener";


/**
 * Cette fonction va permettre de supprimer un produit d'une commande dans la partie back admin du site
 * @param btnsDelete 
 * @return Void
 */
export function deleteContentShoppingCart(btnsDelete: NodeListOf<HTMLButtonElement>): void {
    // On boucle sur les btnsDelete
    btnsDelete.forEach((btn : HTMLButtonElement, key) => {
        // On écoute le click
        btn.addEventListener("click", function(e){

            // On empêche la navigation
            e.preventDefault();

            // On demande confirmation
            if (confirm("Voulez-vous vraiment supprimer ce produit de votre panier ?")){
                // On envoie une requête Ajax vers le href du lien avec la méthode DELETE
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                }).then(
                    // On récupère la réponse en JSON
                    response => response.json()
                ).then(data => {
                    if(data.success) {
                        this.parentElement.parentElement.remove();
                        removePriceTotalLineDelete(key);
                    }
                    else {
                        alert(data.error);
                    }
                }).catch(e => alert(e))
            }
        })
    });
}
