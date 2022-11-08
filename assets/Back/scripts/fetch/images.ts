console.log(('lol'));

window.addEventListener('load', () => {
    // Gestion des boutons "Supprimer"
    let links = document.querySelectorAll("[data-delete]")
    let link: Element;
    

    if (links) {
        // On boucle sur les links
        for(link of links){
            // On écoute le click
            link.addEventListener("click", function(e){
                // On empêche la navigation
                e.preventDefault()
    
                // On demande confirmation
                if(confirm("Voulez-vous vraiment supprimer cette image ?")){
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
                            this.parentElement.remove()
                        }
                        else {
                            alert(data.error)
                        }
                    }).catch(e => alert(e))
                }
            })
        }
    }
});