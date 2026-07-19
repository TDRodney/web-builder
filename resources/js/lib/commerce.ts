import { computed, inject } from 'vue';
import type { ComputedRef, InjectionKey } from 'vue';
import type {
    CommerceHydratedBlock,
    CommerceHydrationEnvelope,
} from '@/types/commerce';

export const emptyCommerceHydration: CommerceHydrationEnvelope = {
    schemaVersion: 1,
    provider: 'null',
    blocks: {},
};

export const commerceHydrationKey: InjectionKey<
    ComputedRef<CommerceHydrationEnvelope>
> = Symbol('commerceHydration');

export function useCommerceBlock<T = unknown>(
    nodeId?: string,
): ComputedRef<CommerceHydratedBlock<T> | undefined> {
    const hydration = inject(
        commerceHydrationKey,
        computed(() => emptyCommerceHydration),
    );

    return computed(() => {
        if (!nodeId) {
            return undefined;
        }

        return hydration.value.blocks[nodeId] as
            | CommerceHydratedBlock<T>
            | undefined;
    });
}
