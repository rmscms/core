document.addEventListener('DOMContentLoaded', function() {
    const scrollContainer = document.querySelector('.chart-scroll-container');
    let isScrolling = false;
    let startX = 0;
    let scrollLeft = 0;

    // Touch events for mobile
    scrollContainer.addEventListener('touchstart', function(e) {
        isScrolling = true;
        startX = e.touches[0].pageX - scrollContainer.offsetLeft;
        scrollLeft = scrollContainer.scrollLeft;
    });

    scrollContainer.addEventListener('touchmove', function(e) {
        if (!isScrolling) return;
        e.preventDefault();
        const x = e.touches[0].pageX - scrollContainer.offsetLeft;
        const walk = (x - startX) * 2;
        scrollContainer.scrollLeft = scrollLeft - walk;
    });

    scrollContainer.addEventListener('touchend', function() {
        isScrolling = false;
    });

    // Mouse events for desktop
    scrollContainer.addEventListener('mousedown', function(e) {
        isScrolling = true;
        startX = e.pageX - scrollContainer.offsetLeft;
        scrollLeft = scrollContainer.scrollLeft;
        scrollContainer.style.cursor = 'grabbing';
    });

    scrollContainer.addEventListener('mousemove', function(e) {
        if (!isScrolling) return;
        e.preventDefault();
        const x = e.pageX - scrollContainer.offsetLeft;
        const walk = (x - startX) * 2;
        scrollContainer.scrollLeft = scrollLeft - walk;
    });

    scrollContainer.addEventListener('mouseup', function() {
        isScrolling = false;
        scrollContainer.style.cursor = 'grab';
    });

    scrollContainer.addEventListener('mouseleave', function() {
        isScrolling = false;
        scrollContainer.style.cursor = 'grab';
    });
});