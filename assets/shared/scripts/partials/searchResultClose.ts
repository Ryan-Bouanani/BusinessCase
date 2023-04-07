
function hideResultSearchBar() {
    // On récupère la div qui affiche les résultats de la searchBar
    const resultSearch: HTMLDivElement = document.querySelector('.resultProductSearch');
    
    // Si elle existe
    if (resultSearch) {
        // On ajoute un évènement click sur le document 
        document.addEventListener('click', function(e: any): void {

            // Si résultats active alors on cache les résultats      
                if (!resultSearch.classList.contains('none') && !e.target.closest('.resultProductSearch')) {
                    resultSearch.classList.add('none')
                }
            },
        )
    }
}
hideResultSearchBar();
