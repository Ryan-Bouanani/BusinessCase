
import { removePriceTotalLineDelete } from "../quantityListener";

export function deletContentShoppingCart(btnsDelete: NodeListOf<HTMLButtonElement>) {
    // On boucle sur les btnsDelete
    btnsDelete.forEach((btn : HTMLButtonElement, key) => {
        // On écoute le click
        btn.addEventListener("click", function(e){
            console.log(btn);
            // On empêche la navigation
            e.preventDefault();
            console.log(btn);
            


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
