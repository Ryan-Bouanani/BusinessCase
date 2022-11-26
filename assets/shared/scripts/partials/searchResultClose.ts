
function hideResultSearchBar() {
    // On récupere la div qui affiche les resultats de la searchBar
    const resultSearch: HTMLDivElement = document.querySelector('.resultProductSearch');
    
    // Si elle existe
    if (resultSearch) {
        // On ajoute un évènement click sur le document 
        document.addEventListener('click', function(e: any): void {

            // Si résultats active alors on cache les resultats      
                if (!resultSearch.classList.contains('none') && !e.target.closest('.resultProductSearch')) {
                    resultSearch.classList.add('none')
                }
            },
        )
    }
}
hideResultSearchBar();
