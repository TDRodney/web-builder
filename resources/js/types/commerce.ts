export interface CommerceMoney {
    amountMinor: number;
    currency: string;
    formatted: string;
}

export interface CommerceImage {
    src: string;
    alt: string;
}

export interface CommerceVariant {
    id: string;
    title: string;
    options: Record<string, string>;
    price: CommerceMoney;
    available: boolean;
}

export interface CommerceProduct {
    id: string;
    handle: string;
    title: string;
    vendor: string;
    description: string;
    category: string;
    badge: string;
    available: boolean;
    url: string;
    images: CommerceImage[];
    price: CommerceMoney;
    compareAtPrice: CommerceMoney | null;
    variants: CommerceVariant[];
}

export interface CommerceCollection {
    id: string;
    handle: string;
    title: string;
    subtitle: string;
    imageSrc: string;
    imageAlt: string;
    url: string;
}

export interface CommerceBinding {
    version: number;
    resource: string;
    source: string;
    options: Record<string, unknown>;
}

export interface CommerceHydratedBlock<T = unknown> {
    binding: CommerceBinding;
    status: 'ready' | 'unavailable' | 'error' | 'loading';
    resource: string;
    source: string;
    data: T | null;
    message: string | null;
}

export interface CommerceHydrationEnvelope {
    schemaVersion: number;
    provider: string;
    blocks: Record<string, CommerceHydratedBlock>;
}

export interface CommerceCartLine {
    id: string;
    variantId: string;
    productId: string;
    title: string;
    variantTitle: string;
    quantity: number;
    image: CommerceImage | null;
    unitPrice: CommerceMoney;
    lineTotal: CommerceMoney;
    available: boolean;
}

export interface CommerceCart {
    id: string;
    provider: string;
    currency: string;
    lines: CommerceCartLine[];
    itemCount: number;
    subtotal: CommerceMoney;
    total: CommerceMoney;
    checkoutAvailable: boolean;
}
