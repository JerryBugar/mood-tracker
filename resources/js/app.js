import './bootstrap';
import '../css/app.css';

document.addEventListener('DOMContentLoaded', () => {
    const splashLogo = document.getElementById('splash-logo-container');
    const mainContent = document.getElementById('main-content-wrapper');
    const finalLogo = document.getElementById('final-logo-position');

    if (!splashLogo || !mainContent || !finalLogo) {
        return;
    }

    requestAnimationFrame(() => {
        // 1. Measure the final position
        mainContent.classList.remove('hidden');
        const finalRect = finalLogo.getBoundingClientRect();
        mainContent.classList.add('hidden');

        // Calculate the destination transform relative to the viewport center
        const finalX = finalRect.left + finalRect.width / 2 - window.innerWidth / 2;
        const finalY = finalRect.top + finalRect.height / 2 - window.innerHeight / 2;

        // 2. Create dynamic keyframes that only animate position (transform)
        const styleSheet = document.createElement('style');
        styleSheet.id = 'dynamic-splash-animation';
        const keyframes = `
            @keyframes settleInPlace {
                0% {
                    transform: translate(-50%, -50%);
                    opacity: 1;
                }
                100% {
                    transform: translate(calc(-50% + ${finalX}px), calc(-50% + ${finalY}px));
                    opacity: 1;
                }
            }
        `;
        styleSheet.innerHTML = keyframes;
        document.head.appendChild(styleSheet);

        // 3. Make the splash logo visible and run the animation
        splashLogo.style.opacity = '1';
        splashLogo.style.animation = 'settleInPlace 2.5s ease-in-out forwards';

        // 4. Clean up after animation
        splashLogo.addEventListener('animationend', () => {
            finalLogo.style.opacity = 0; // Hide the original logo to prevent flashing
            mainContent.classList.remove('hidden');
            
            // Fade in the original logo and fade out the splash logo
            setTimeout(() => {
                finalLogo.style.transition = 'opacity 0.5s ease-in';
                finalLogo.style.opacity = 1;
                splashLogo.style.opacity = 0;
            }, 50);

            // Remove the splash logo after it has faded out
            splashLogo.addEventListener('transitionend', () => {
                splashLogo.remove();
                document.getElementById('dynamic-splash-animation').remove();
            });
        });
    });
});