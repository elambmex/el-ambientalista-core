// js/main-carousel.js
document.addEventListener('DOMContentLoaded', function () {
    // Asegurarse de que el widget de Elementor está presente
    if (document.querySelector('.nyt-liveblog-swiper')) {
        
        const swiper = new Swiper('.nyt-liveblog-swiper', {
            // Opciones del carrusel
            direction: 'horizontal',
            loop: false,
            
            // Cuántos slides mostrar
            slidesPerView: 1,
            spaceBetween: 20,
            
            // Paginación (los puntos)
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },

            // Responsive (Opcional, pero recomendado)
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 40,
                }
            }
        });
    }
});