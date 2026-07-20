/**
 * Resolve a stored color prop into a CSS value.
 * Theme tokens are stored as bare custom-property names (e.g. `--theme-primary`)
 * and must be wrapped in var() before hitting the DOM.
 */
export function resolveThemeColor(
    value: unknown,
    fallback: string | null = null,
): string | null {
    if (value === undefined || value === null || value === '') {
        return fallback;
    }

    const raw = String(value);

    return raw.startsWith('--') ? `var(${raw})` : raw;
}
