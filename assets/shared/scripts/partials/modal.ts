
// modal variables
const modalElem: NodeListOf<Element> = document.querySelectorAll('[data-modal]');
const btnModalActive: NodeListOf<HTMLButtonElement> = document.querySelectorAll('[data-active-modal]');
const btnModalClose: NodeListOf<Element> = document.querySelectorAll('[data-modal-close-open]');
const modalOverlay = document.querySelectorAll('[data-modal-overlay]');


// modal close function
function modalCloseFunction(key: number, e: Event) { 
    e.preventDefault,
    modalElem[key].classList.add('overlayClose');
    
}
// modal  open function
function modalOpenFunction(key: number, e: Event): void { 
    e.preventDefault,
    
    modalElem[key].classList.remove('overlayClose');
}

if (modalOverlay && btnModalClose && btnModalActive) {
    
    // modal eventListener
    modalOverlay.forEach((overlay, key) => {    
        overlay.addEventListener('click', (e: Event) => {
            modalCloseFunction(key, e);      
        })
    });
    btnModalClose.forEach((btnClose, key) => {    
        btnClose.addEventListener('click', (e: Event) => {
            modalCloseFunction(key, e);      
        })
    });
    btnModalActive.forEach((btnActive, key) => {    
        btnActive.addEventListener('click', (e: Event) => {
            modalOpenFunction(key, e);      
        })
    });

}