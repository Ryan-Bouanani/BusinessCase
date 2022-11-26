
// modal variables
const modalElem: Element = document.querySelector('[data-modal]');
const btnModalActive: HTMLButtonElement = document.querySelector('[data-active-modal]');
const btnModalClose: Element = document.querySelector('[data-modal-close-open]');
const modalOverlay = document.querySelector('[data-modal-overlay]');


// modal close function
function modalCloseFunction(e: Event) { 
    e.preventDefault,
    modalElem.classList.add('overlayClose');
    
}
// modal  open function
function modalOpenFunction(e: Event) { 
    e.preventDefault,
    console.log(modalOverlay);
    
    modalElem.classList.remove('overlayClose');
    
}

if (modalOverlay && btnModalClose && btnModalActive) {
    
    // modal eventListener
    modalOverlay.addEventListener('click', modalCloseFunction);
    btnModalClose.addEventListener('click', modalCloseFunction);
    btnModalActive.addEventListener('click', modalOpenFunction);
}