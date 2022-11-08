
// modal variables
const modalElem: Element = document.querySelector('[data-modal]');
const btnModalActive: HTMLButtonElement = document.querySelector('[data-active-modal]');
const btnModalClose: Element = document.querySelector('[data-modal-close-open]');
const modalOverlay = document.querySelector('[data-modal-overlay]');

console.log('lol');

// modal close function
function modalCloseFunction(e: Event) { 
    e.preventDefault,
    modalOverlay.classList.add('overlayClose');
    
}
// modal  open function
function modalOpenFunction(e: Event) { 
    e.preventDefault,
    console.log(e);
    
    modalOverlay.classList.remove('close');
    
}
console.log();

if (modalOverlay && btnModalClose && btnModalActive) {
    
    // modal eventListener
    modalOverlay.addEventListener('click', modalCloseFunction);
    btnModalClose.addEventListener('click', modalCloseFunction);
    btnModalActive.addEventListener('click', modalOpenFunction);
}