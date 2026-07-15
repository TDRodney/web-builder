import { computed, inject, ref } from 'vue';
import type { ComputedRef, InjectionKey, Ref } from 'vue';
import type { CommerceCart } from '@/types/commerce';

export interface CommerceCartContext {
    cart: Ref<CommerceCart | null>;
    itemCount: ComputedRef<number>;
    isOpen: Ref<boolean>;
    isBusy: Ref<boolean>;
    error: Ref<string>;
    open: () => void;
    close: () => void;
    add: (variantId: string, quantity?: number) => Promise<boolean>;
    update: (variantId: string, quantity: number) => Promise<boolean>;
    remove: (variantId: string) => Promise<boolean>;
    checkout: () => Promise<boolean>;
}

export const commerceCartKey: InjectionKey<CommerceCartContext> =
    Symbol('commerceCart');

const csrfToken = (): string => {
    if (typeof document === 'undefined') {
        return '';
    }

    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
};

export function createCommerceCart(
    initialCart: CommerceCart | null,
): CommerceCartContext {
    const cart = ref<CommerceCart | null>(initialCart);
    const isOpen = ref(false);
    const isBusy = ref(false);
    const error = ref('');
    const itemCount = computed(() => cart.value?.itemCount || 0);

    const request = async (
        url: string,
        method: string,
        body?: Record<string, unknown>,
    ): Promise<Record<string, any> | null> => {
        isBusy.value = true;
        error.value = '';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': csrfToken(),
                },
                body: body ? JSON.stringify(body) : undefined,
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                error.value =
                    payload.message || 'The cart could not be updated.';

                return null;
            }

            if (payload.cart) {
                cart.value = payload.cart;
            }

            return payload;
        } catch {
            error.value = 'The cart service is currently unavailable.';

            return null;
        } finally {
            isBusy.value = false;
        }
    };

    return {
        cart,
        itemCount,
        isOpen,
        isBusy,
        error,
        open: () => {
            isOpen.value = true;
        },
        close: () => {
            isOpen.value = false;
        },
        add: async (variantId: string, quantity = 1): Promise<boolean> => {
            const result = await request('/commerce/cart/lines', 'POST', {
                variant_id: variantId,
                quantity,
            });

            if (result) {
                isOpen.value = true;
            }

            return result !== null;
        },
        update: async (variantId: string, quantity: number): Promise<boolean> =>
            (await request(
                `/commerce/cart/lines/${encodeURIComponent(variantId)}`,
                'PATCH',
                { quantity },
            )) !== null,
        remove: async (variantId: string): Promise<boolean> =>
            (await request(
                `/commerce/cart/lines/${encodeURIComponent(variantId)}`,
                'DELETE',
            )) !== null,
        checkout: async (): Promise<boolean> => {
            const result = await request('/commerce/checkout', 'POST');

            if (result?.checkoutUrl && typeof window !== 'undefined') {
                window.location.assign(result.checkoutUrl);

                return true;
            }

            return false;
        },
    };
}

export function useCommerceCart(): CommerceCartContext | null {
    return inject(commerceCartKey, null);
}
