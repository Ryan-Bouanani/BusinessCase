
const openMenuIcon = document.querySelector('.top-nav .search-menu-mobile .menu');
const closeMenuIcon = document.querySelector('.bottom-nav .top-nav-mobile .close');
const navbar = document.querySelector('header .bottom-nav');

console.log(closeMenuIcon);
openMenuIcon.addEventListener('click', () => {
    navbar.classList.add('open');
    navbar.classList.remove('close');
});
closeMenuIcon.addEventListener('click', () => {
    navbar.classList.remove('open');
    navbar.classList.add('close');
});


let cardContainers = [...document.querySelectorAll('.carrousel')];
let preBtn = [...document.querySelectorAll('.arrow_left')];
let nxtBtn = [...document.querySelectorAll('.arrow_right')];

cardContainers.forEach((item, i) => {
    // on récuper la width de notre container carrousel
    let containerDimensions = item.getBoundingClientRect();
    let containerWidth = containerDimensions.width;

    nxtBtn[i].addEventListener('click', () => {
        item.scrollLeft += containerWidth;
    })
    preBtn[i].addEventListener('click', () => {
        item.scrollLeft -= containerWidth;
    })
})

const category = document.querySelectorAll('.category');
const linkCategory = [...document.querySelectorAll('.linkCategory i.fa-chevron-right')];
const subMenu = document.querySelectorAll('.sub-menu');

linkCategory.forEach((icon, i) => {
    icon.addEventListener('click', () => {
        console.log(subMenu[i].classList.contains('sub-menu-active'));
       
            subMenu[i].classList.toggle('sub-menu-active');
            icon.classList.toggle('rotate');
            console.log('else');
        
    })
});