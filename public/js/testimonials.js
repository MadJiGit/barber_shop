/**
 * Testimonials Carousel
 * Loads testimonials from JSON and initializes Slick carousel
 */

document.addEventListener('DOMContentLoaded', function() {
    const testimonialContainer = document.querySelector('.testimonial-active');

    if (!testimonialContainer) {
        return;
    }

    // Get current locale from HTML lang attribute
    const locale = document.documentElement.lang || 'bg';

    // Fetch testimonials from JSON based on locale
    fetch(`/data/testimonials.${locale}.json`)
        .then(response => response.json())
        .then(testimonials => {
            // Clear existing content
            testimonialContainer.innerHTML = '';

            // Build testimonial slides
            testimonials.forEach(testimonial => {
                const slide = document.createElement('div');
                slide.className = 'single-testimonial text-center';
                slide.innerHTML = `
                    <div class="testimonial-caption">
                        <div class="testimonial-top-cap">
                            <p>"${escapeHtml(testimonial.text)}"</p>
                        </div>
                        <div class="testimonial-founder d-flex align-items-center justify-content-center">
                            <div class="founder-text">
                                <span>${escapeHtml(testimonial.name)}</span>
                                <p>${escapeHtml(testimonial.role)}</p>
                            </div>
                        </div>
                    </div>
                `;
                testimonialContainer.appendChild(slide);
            });

            // Initialize Slick carousel after content is loaded
            if (typeof $.fn.slick !== 'undefined') {
                $(testimonialContainer).slick({
                    dots: true,
                    infinite: true,
                    speed: 800,
                    autoplay: true,
                    autoplaySpeed: 5000, // 5 seconds
                    arrows: true,
                    prevArrow: '<button type="button" class="slick-prev"><i class="ti-angle-left"></i></button>',
                    nextArrow: '<button type="button" class="slick-next"><i class="ti-angle-right"></i></button>',
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    pauseOnHover: true,
                    responsive: [
                        {
                            breakpoint: 768,
                            settings: {
                                arrows: false,
                                dots: true
                            }
                        }
                    ]
                });
            }
        })
        .catch(error => {
            console.error('Error loading testimonials:', error);
        });
});

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
