import type { Directive } from 'vue';

export const REVEAL_TYPES = [
    'fade-up',
    'fade-in',
    'scale-in',
    'slide-left',
    'slide-right',
] as const;

export type RevealType = (typeof REVEAL_TYPES)[number];

export interface RevealBinding {
    type?: string | null;
    delay?: number | null;
}

let observer: IntersectionObserver | null = null;

const getObserver = (): IntersectionObserver | null => {
    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) {
        return null;
    }

    if (!observer) {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal-visible');
                        observer?.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -8% 0px' },
        );
    }

    return observer;
};

const prefersReducedMotion = (): boolean =>
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const isRevealType = (value: unknown): value is RevealType =>
    typeof value === 'string' && (REVEAL_TYPES as readonly string[]).includes(value);

const cleanup = (el: HTMLElement): void => {
    observer?.unobserve(el);
    el.classList.remove(
        'reveal',
        'reveal-visible',
        ...REVEAL_TYPES.map((type) => `reveal-${type}`),
    );
    el.style.removeProperty('--reveal-delay');
};

/**
 * v-reveal directive. Pass `{ type, delay }` where `type` is one of REVEAL_TYPES.
 * A nullish or unknown type is a no-op so blocks without animation render untouched.
 * The initial hidden state is only applied client-side, so SSR output stays visible.
 */
export const vReveal: Directive<HTMLElement, RevealBinding | null | undefined> = {
    mounted(el, binding) {
        const type = binding.value?.type;

        if (!isRevealType(type)) {
            return;
        }

        const io = getObserver();

        if (!io || prefersReducedMotion()) {
            return;
        }

        const delay = Number(binding.value?.delay) || 0;

        if (delay > 0) {
            el.style.setProperty('--reveal-delay', `${Math.min(delay, 1200)}ms`);
        }

        el.classList.add('reveal', `reveal-${type}`);
        io.observe(el);
    },
    updated(el, binding) {
        const previous = binding.oldValue?.type;
        const next = binding.value?.type;

        if (previous === next) {
            return;
        }

        cleanup(el);

        if (isRevealType(next)) {
            const io = getObserver();

            if (!io || prefersReducedMotion()) {
                return;
            }

            const delay = Number(binding.value?.delay) || 0;

            if (delay > 0) {
                el.style.setProperty('--reveal-delay', `${Math.min(delay, 1200)}ms`);
            }

            el.classList.add('reveal', `reveal-${next}`);
            io.observe(el);
        }
    },
    unmounted(el) {
        observer?.unobserve(el);
    },
};
