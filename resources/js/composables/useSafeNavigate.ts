import type { VisitOptions } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { inject } from 'vue';
import { toast } from 'vue-sonner';

type ForceSave = (() => Promise<void> | void) | null;

export type UseSafeNavigateReturn = {
    /**
     * Flush any pending editor draft save, then perform an Inertia visit.
     *
     * Enforces the editor's save-before-switch contract: an internal editor
     * exit must never navigate away with unsaved draft state. Returns true
     * when navigation proceeded and false when the flush failed (so the
     * caller can keep the user in place instead of abandoning their work).
     */
    safeNavigate: (
        url: string,
        options?: VisitOptions,
    ) => Promise<boolean>;
};

export function useSafeNavigate(): UseSafeNavigateReturn {
    const forceSave = inject<ForceSave>('forceSave', null);

    const safeNavigate = async (
        url: string,
        options: VisitOptions = {},
    ): Promise<boolean> => {
        if (!forceSave) {
            router.visit(url, options);

            return true;
        }

        const loadToast = toast.loading('Saving draft...');

        try {
            await forceSave();
        } catch {
            toast.error('Failed to save draft before navigating', {
                id: loadToast,
            });

            return false;
        }

        toast.success('Draft saved successfully', { id: loadToast });
        router.visit(url, options);

        return true;
    };

    return { safeNavigate };
}
