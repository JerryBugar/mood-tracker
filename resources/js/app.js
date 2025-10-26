import * as Turbo from '@hotwired/turbo';
import './bootstrap';
import '../css/app.css';
import 'bootstrap'; // Import Bootstrap JavaScript

let isNavInitialized = false;

document.addEventListener('turbo:load', () => {
    // Splash screen logic from original file
    const splashLogo = document.getElementById('splash-logo-container');
    const mainContent = document.getElementById('main-content-wrapper');
    const finalLogo = document.getElementById('final-logo-position');

    if (splashLogo && mainContent && finalLogo) {
        if (!splashLogo.classList.contains('js-has-run')) {
            splashLogo.classList.add('js-has-run');
            requestAnimationFrame(() => {
                mainContent.classList.remove('hidden');
                const finalRect = finalLogo.getBoundingClientRect();
                mainContent.classList.add('hidden');
        
                const finalX = finalRect.left + finalRect.width / 2 - window.innerWidth / 2;
                const finalY = finalRect.top + finalRect.height / 2 - window.innerHeight / 2;
        
                const styleSheet = document.createElement('style');
                styleSheet.id = 'dynamic-splash-animation';
                const keyframes = `
                    @keyframes settleInPlace {
                        0% { transform: translate(-50%, -50%); opacity: 1; }
                        100% { transform: translate(calc(-50% + ${finalX}px), calc(-50% + ${finalY}px)); opacity: 1; }
                    }
                `;
                styleSheet.innerHTML = keyframes;
                document.head.appendChild(styleSheet);
        
                splashLogo.style.opacity = '1';
                splashLogo.style.animation = 'settleInPlace 2.5s ease-in-out forwards';
        
                splashLogo.addEventListener('animationend', () => {
                    finalLogo.style.opacity = 0;
                    mainContent.classList.remove('hidden');
                    
                    setTimeout(() => {
                        finalLogo.style.transition = 'opacity 0.5s ease-in';
                        finalLogo.style.opacity = 1;
                        splashLogo.style.opacity = 0;
                    }, 50);
        
                    splashLogo.addEventListener('transitionend', () => {
                        splashLogo.remove();
                        if (document.getElementById('dynamic-splash-animation')) {
                            document.getElementById('dynamic-splash-animation').remove();
                        }
                    });
                });
            });
        }
    }

    // --- Definitive Sliding Nav Logic ---
    const bottomNav = document.querySelector('#main-bottom-nav');
    if (!bottomNav) return;

    // Manually manage the active state based on URL
    const navItems = bottomNav.querySelectorAll('.nav-item');
    const currentPath = window.location.pathname;
    let newActiveItem = null;

    navItems.forEach(item => {
        item.classList.remove('active');
        const itemPath = new URL(item.href).pathname;
        if (itemPath === currentPath) {
            newActiveItem = item;
        }
    });

    if (newActiveItem) {
        newActiveItem.classList.add('active');
    }

    // Wait for the .nav-item's own CSS transition to finish before measuring its width.
    setTimeout(() => {
        const activeBackground = bottomNav.querySelector('.nav-active-background');
        const activeItem = bottomNav.querySelector('.nav-item.active');

        if (activeBackground && activeItem) {
            if (!isNavInitialized) {
                // On first load, just snap to position without animation
                activeBackground.style.transition = 'none';
                activeBackground.style.left = `${activeItem.offsetLeft}px`;
                activeBackground.style.width = `${activeItem.offsetWidth}px`;
                activeBackground.style.opacity = 1;
                
                setTimeout(() => {
                    activeBackground.style.transition = 'all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55)';
                    isNavInitialized = true;
                }, 50);

            } else {
                // On subsequent loads, the transition is already enabled. Just move it.
                activeBackground.style.left = `${activeItem.offsetLeft}px`;
                activeBackground.style.width = `${activeItem.offsetWidth}px`;
            }
        }
    }, 200); // .nav-item transition is 0.3s, so we wait 200ms.
});