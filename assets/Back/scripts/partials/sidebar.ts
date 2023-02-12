let linkSidebar = document.querySelectorAll('aside a');
if (linkSidebar) {
    
    for (let i = 0; i < linkSidebar.length; i++) {
      linkSidebar[i].addEventListener('click', () => {
        let currentLink = document.querySelector('aside a.active');
        if (currentLink) {
          currentLink.classList.remove('active');
          linkSidebar[i].classList.add('active');
        }
      })
    }
}

let menu = document.querySelector('.containerBack .menu');
let aside = document.querySelector('.containerBack aside');
let closeAside = document.querySelector('.containerBack .close');

menu.addEventListener('click', () => {
    console.log('lolll');
    
    aside.classList.toggle('menuDisplay');
});
closeAside.addEventListener('click', () => {
    aside.classList.toggle('menuDisplay');
    // navbar.classList.add('close');
});