document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.property-carousel').forEach(function (carousel) {
        var track = carousel.querySelector('.property-carousel__track');
        var slides = carousel.querySelectorAll('.property-carousel__slide');
        var dots = carousel.querySelectorAll('.property-carousel__dot');
        var prevBtn = carousel.querySelector('.property-carousel__arrow--prev');
        var nextBtn = carousel.querySelector('.property-carousel__arrow--next');
        var current = 0;

        function goTo(index) {
            current = Math.max(0, Math.min(index, slides.length - 1));
            track.scrollTo({ left: track.clientWidth * current, behavior: 'smooth' });
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === current);
            });
        }

        if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); });
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goTo(i); });
        });

        carousel.setAttribute('tabindex', '0');
        carousel.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') goTo(current - 1);
            if (e.key === 'ArrowRight') goTo(current + 1);
        });
    });
});
