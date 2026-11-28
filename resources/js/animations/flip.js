import { gsap } from 'gsap';

/** Initialize the vehicle photo flip and desktop hover motion. */
export function initCardFlip({ card, inner, back, reducedMotion = false }) {
    if (!card || !inner || !back) {
        return { flip() {}, destroy() {} };
    }

    gsap.set(inner, { rotationY: 0 });
    gsap.set(back, { rotationY: 180 });

    let isOpen = false;
    const hoverEnabled = !reducedMotion
        && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    const onMouseEnter = () => {
        gsap.to(card, { y: -6, duration: 0.3, ease: 'power2.out' });
    };
    const onMouseLeave = () => {
        gsap.to(card, { y: 0, duration: 0.3, ease: 'power2.out' });
    };

    if (hoverEnabled) {
        card.addEventListener('mouseenter', onMouseEnter);
        card.addEventListener('mouseleave', onMouseLeave);
    }

    function flip(open) {
        const nextOpen = Boolean(open);
        if (nextOpen === isOpen) return;
        isOpen = nextOpen;

        // Stop an in-progress flip before starting the latest requested state.
        gsap.killTweensOf([card, inner]);

        if (reducedMotion) {
            gsap.set(card, { scale: 1, y: 0 });
            gsap.set(inner, { rotationY: isOpen ? 180 : 0 });
            return;
        }

        gsap.timeline()
            .to(card, { scale: 1.06, duration: 0.25, ease: 'power2.out' })
            .to(card, { scale: 1, duration: 0.45, ease: 'power2.inOut' }, 0.4)
            .to(inner, {
                rotationY: isOpen ? 180 : 0,
                duration: 0.8,
                ease: 'back.out(1.6)',
            }, 0);
    }

    function destroy() {
        if (hoverEnabled) {
            card.removeEventListener('mouseenter', onMouseEnter);
            card.removeEventListener('mouseleave', onMouseLeave);
        }
        gsap.killTweensOf([card, inner]);
    }

    return { flip, destroy };
}