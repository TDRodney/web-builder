import { computed, watch, type CSSProperties } from 'vue';

interface ThemeColors {
    primary?: string;
    secondary?: string;
    background?: string;
    text?: string;
}

interface ThemeTypography {
    headingFont?: string;
    bodyFont?: string;
}

interface ThemeConfig {
    colors?: ThemeColors;
    typography?: ThemeTypography;
    borderRadius?: string;
}

const DEFAULT_THEME: Required<ThemeConfig & {
    colors: Required<ThemeColors>;
    typography: Required<ThemeTypography>;
}> = {
    colors: { primary: '#4f46e5', secondary: '#0ea5e9', background: '#ffffff', text: '#0f172a' },
    typography: { headingFont: 'Instrument Sans', bodyFont: 'Instrument Sans' },
    borderRadius: '8px',
};

const FONT_LINK_ID = 'theme-google-fonts';

function buildFontUrl(fonts: string[]): string {
    const families = fonts
        .map((f) => `family=${encodeURIComponent(f).replace(/%20/g, '+')}:wght@400;500;600;700`)
        .join('&');
    return `https://fonts.googleapis.com/css2?${families}&display=swap`;
}

export function useTheme(themeGetter: () => ThemeConfig | null | undefined) {
    const theme = computed<ThemeConfig | null | undefined>(themeGetter);

    const cssVars = computed<CSSProperties>(() => {
        const t = theme.value;
        const colors = t?.colors ?? {};
        const typography = t?.typography ?? {};

        return {
            '--theme-primary': colors.primary ?? DEFAULT_THEME.colors.primary,
            '--theme-secondary': colors.secondary ?? DEFAULT_THEME.colors.secondary,
            '--theme-bg': colors.background ?? DEFAULT_THEME.colors.background,
            '--theme-text': colors.text ?? DEFAULT_THEME.colors.text,
            '--theme-border-radius': t?.borderRadius ?? DEFAULT_THEME.borderRadius,
            '--theme-font-heading': typography.headingFont ?? DEFAULT_THEME.typography.headingFont,
            '--theme-font-body': typography.bodyFont ?? DEFAULT_THEME.typography.bodyFont,
        } as CSSProperties;
    });

    const fontUrl = computed(() => {
        const t = theme.value;
        const heading = t?.typography?.headingFont ?? DEFAULT_THEME.typography.headingFont;
        const body = t?.typography?.bodyFont ?? DEFAULT_THEME.typography.bodyFont;
        const fonts = [...new Set([heading, body])];
        return buildFontUrl(fonts);
    });

    if (typeof window !== 'undefined') {
        watch(
            fontUrl,
            (url) => {
                let link = document.getElementById(FONT_LINK_ID) as HTMLLinkElement | null;
                if (!link) {
                    link = document.createElement('link');
                    link.id = FONT_LINK_ID;
                    link.rel = 'stylesheet';
                    document.head.appendChild(link);
                }
                if (link.href !== url) {
                    link.href = url;
                }
            },
            { immediate: true }
        );
    }

    return { theme, cssVars, fontUrl };
}
