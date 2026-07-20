/**
 * Shared layout surface styles for LayoutGrid / LayoutColumn.
 * Applied to the children wrapper (canvas draggable / public children box)
 * so nested blocks become real grid/flex items instead of stacking inside
 * a single wrapper child.
 */

export function gridSurfaceStyles(
    blockProps: Record<string, unknown> | null | undefined,
    options: { isEmpty?: boolean; isEditable?: boolean } = {},
): Record<string, string | number | undefined> {
    const props = blockProps ?? {};
    const columns = Number(props.columns) || 3;
    const gapValue = (props.gap as string | number | undefined) ?? '1rem';
    const widthValue = props.width as string | number | undefined;
    const heightValue = props.height as string | number | undefined;
    const columnTemplate = (props.columnTemplate as string) ?? 'equal';
    const alignItems = (props.alignItems as string) ?? 'stretch';
    const stackOnNarrow =
        props.stackOnNarrow !== false && props.stackOnNarrow !== 'false';

    const templateValue =
        columnTemplate === 'wide-left'
            ? 'minmax(0, 1.2fr) minmax(0, 1fr)'
            : columnTemplate === 'wide-right'
              ? 'minmax(0, 1fr) minmax(0, 1.2fr)'
              : columnTemplate === 'auto-fit'
                ? 'repeat(auto-fit, minmax(min(100%, 11rem), 1fr))'
                : `repeat(${columns}, minmax(0, 1fr))`;

    return {
        display: 'grid',
        gridTemplateColumns: templateValue,
        alignItems,
        gap: typeof gapValue === 'number' ? `${gapValue}px` : gapValue,
        width:
            widthValue !== undefined && widthValue !== null
                ? typeof widthValue === 'number'
                    ? `${widthValue}px`
                    : widthValue
                : '100%',
        height:
            heightValue !== undefined && heightValue !== null
                ? typeof heightValue === 'number'
                    ? `${heightValue}px`
                    : heightValue
                : 'auto',
        minHeight:
            options.isEditable && options.isEmpty ? '50px' : undefined,
        ['--stack-on-narrow' as string]: stackOnNarrow ? '1' : '0',
    };
}

export function columnSurfaceStyles(
    blockProps: Record<string, unknown> | null | undefined,
    options: { isEmpty?: boolean; isEditable?: boolean } = {},
): Record<string, string | number | undefined> {
    const props = blockProps ?? {};
    const span = props.span as string | number | undefined;
    const widthValue = props.width as string | number | undefined;
    const heightValue = props.height as string | number | undefined;
    const gapValue = props.gap as string | number | undefined;
    const verticalAlign = (props.verticalAlign as string) ?? 'start';
    const horizontalAlign = (props.horizontalAlign as string) ?? 'stretch';

    const styles: Record<string, string | number | undefined> = {
        display: 'flex',
        flexDirection: 'column',
        justifyContent: verticalAlign,
        alignItems: horizontalAlign,
        gap:
            gapValue !== undefined && gapValue !== null
                ? typeof gapValue === 'number'
                    ? `${gapValue}px`
                    : gapValue
                : '0px',
        width:
            widthValue !== undefined && widthValue !== null
                ? typeof widthValue === 'number'
                    ? `${widthValue}px`
                    : widthValue
                : '100%',
        height:
            heightValue !== undefined && heightValue !== null
                ? typeof heightValue === 'number'
                    ? `${heightValue}px`
                    : heightValue
                : 'auto',
        minHeight:
            options.isEditable && options.isEmpty ? '50px' : undefined,
        ['--stack-on-narrow' as string]: '0',
    };

    if (span) {
        if (
            typeof span === 'string' &&
            (span.includes('%') || span.includes('px') || span.includes('/'))
        ) {
            styles.flexBasis = span;
        } else {
            styles.gridColumn = `span ${span} / span ${span}`;
        }
    }

    return styles;
}

export function surfaceStylesForBlock(
    type: string,
    blockProps: Record<string, unknown> | null | undefined,
    options: { isEmpty?: boolean; isEditable?: boolean } = {},
): Record<string, string | number | undefined> | null {
    if (type === 'LayoutGrid') {
        return gridSurfaceStyles(blockProps, options);
    }

    if (type === 'LayoutColumn') {
        return columnSurfaceStyles(blockProps, options);
    }

    return null;
}

export function surfaceIsGrid(
    styles: Record<string, string | number | undefined> | null,
): boolean {
    return styles?.display === 'grid';
}

export function surfaceStacksOnNarrow(
    styles: Record<string, string | number | undefined> | null,
): boolean {
    return styles?.['--stack-on-narrow'] === '1';
}
