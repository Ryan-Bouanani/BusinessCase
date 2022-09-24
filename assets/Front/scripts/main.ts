
// * navbar toggle
const openMenuIcon = document.querySelector('.top-nav .search-menu-mobile .menu');
const closeMenuIcon = document.querySelector('.bottom-nav .top-nav-mobile .close');
const navbar = document.querySelector('header .bottom-nav');

openMenuIcon.addEventListener('click', () => {
    navbar.classList.add('open');
    navbar.classList.remove('close');
});
closeMenuIcon.addEventListener('click', () => {
    navbar.classList.remove('open');
    navbar.classList.add('close');
});



// * carrousel products
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


// * category sidebar
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


// * Carrousel testimonials
const reviewsContainer = document.querySelector('.testimonials-slider');
const reviewsItems = [...document.querySelectorAll('.testimonial-item')];
let arrowLeft = document.querySelector('.testimonials-slider .arrow_left');
let arrowRight = document.querySelector('.testimonials-slider .arrow_right');
const testimonialControls = [arrowLeft, arrowRight];
let slideIndex = 2;

class Carousel3d {

    carrouselContainer: Element;
    carrouselArray: Array<Element>;
    carrouselControls: Array<Element>;
    elementIsMidle = true;

    constructor(container: Element, items: Array<Element>, controls: Array<Element>) {
        this.carrouselContainer = container;
        this.carrouselArray = [...items];
        this.carrouselControls = controls;
        // todo ajouter la class main a celui qui a un data-index à 3
    }

    updateGallery() {
        if (this.elementIsMidle) {  
            reviewsItems[slideIndex].classList.add('main'); 
            this.elementIsMidle = false;
        }

        this.carrouselArray.forEach((element) => {
            element.classList.remove(`testimonial-item-1`);         
            element.classList.remove(`testimonial-item-2`);         
            element.classList.remove(`testimonial-item-3`);         
            element.classList.remove(`testimonial-item-4`);         
            element.classList.remove(`testimonial-item-5`);               
        });
        

        this.carrouselArray.slice(0, 5).forEach((element, i) => {
            element.classList.add(`testimonial-item-${i+1}`);
            // if (i == 2) {
            //     console.log(reviewsItems[i]);
                
            //     reviewsItems[i].classList.add('main'); 
            //     this.carrouselArray[i - 1].classList.remove('main');
            // }
        });
    };

    setCurrentState(direction: Element) {
        this.carrouselArray[slideIndex].classList.remove('main');
        if (direction.className == 'arrow_right') {
            this.carrouselArray.push(this.carrouselArray.shift());
        } else {
            this.carrouselArray.unshift(this.carrouselArray.pop());
        }
        this.updateGallery();
        this.carrouselArray[slideIndex].classList.add('main'); 
    }



    useControls() {
        const triggers = this.carrouselControls;
        console.log(this.carrouselControls);
        
        triggers.forEach(control => {
            control.addEventListener('click', () => {
                this.setCurrentState(control);
            })
        });
    }
};

const carrouselTestimonials  = new Carousel3d(reviewsContainer, reviewsItems, testimonialControls);

carrouselTestimonials.updateGallery()
carrouselTestimonials.useControls();



// Arrow scroll top and appareance on scroll
const arrowScrollTop = document.querySelector('.arrowScrollTop');

window.onscroll = function() {
    appareanceArrowScrollTop();
};

function scrollTop() {
    window.scrollTo(0, 0)
    console.log(arrowScrollTop);
}
arrowScrollTop.addEventListener('click', scrollTop);

function appareanceArrowScrollTop() {
    if (window.pageYOffset >= 1800) {
      arrowScrollTop.classList.add("display")
    } else {
      arrowScrollTop.classList.remove("display");
    }
  }
