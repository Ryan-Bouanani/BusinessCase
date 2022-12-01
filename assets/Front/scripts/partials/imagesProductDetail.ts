
const imagesSecondaries = [...document.querySelectorAll('.imagesSecondary img')] as Array<HTMLImageElement>;
const mainImage = document.querySelector('.imageMain img') as HTMLImageElement;

if (imagesSecondaries) {
    imagesSecondaries.forEach(imgSecondary => {
        
        imgSecondary.addEventListener('click', function() {
            mainImage.src = imgSecondary.src;
            mainImage.alt = imgSecondary.alt;
        });
    });
}