window.addEventListener('load', () => {
    // On récupere nos étoiles
    const stars = document.querySelectorAll('.modalContent .fa-star');
    let star: Element;
    // On va chercher l'input  
    const note = document.querySelector('#review_note') as HTMLInputElement;

    // On va boucler sur les étoiles pour leurs ajouter des écouteurs d'évenements
    for(star of stars) {
        // On écoute le survol
        star.addEventListener('mouseover', function() {
            resetStars();
            this.classList.add('yellow');

            // L'élement précédent dans le dom (de mêmeniveau)
            let previousStar: Element = this.previousElementSibling;
            
            while (previousStar) {
                // On passe l'étoile qui précede en jaune et on récupere l'étoile qui la précede
                previousStar.classList.add('yellow');
                previousStar = previousStar.previousElementSibling;
            }        
        })         
        // On écoute le clic
        star.addEventListener('click', function() {
            note.value = this.dataset.value;
            
        })
         // On écoute le survol
         star.addEventListener('mouseout', function() {
            resetStars(+note.value);
         })
    }

    function resetStars(note: number = 0) {
        for(star of stars) {
            if (+(star as HTMLElement).dataset.value > note) {           
                console.log(star);
                star.classList.remove('yellow');
                
            } else {
                star.classList.add('yellow')
            }
        }
    }
    
});
