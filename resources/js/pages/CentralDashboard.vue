<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    tenant?: {
        id: number;
        subdomain: string;
    } | null;
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const tenantEditorUrl = computed(() => {
    if (!props.tenant) {
return '';
}

    const protocol = window.location.protocol;
    const hostParts = window.location.host.split('.');
    // If running on domain.localhost:8000, we want to construct subdomain.domain.localhost:8000
    const baseHost = hostParts.length > 2 ? hostParts.slice(-2).join('.') : window.location.host;

    return `${protocol}//${props.tenant.subdomain}.${baseHost}/editor`;
});

const tenantPublicUrl = computed(() => {
    if (!props.tenant) {
return '';
}

    const protocol = window.location.protocol;
    const hostParts = window.location.host.split('.');
    const baseHost = hostParts.length > 2 ? hostParts.slice(-2).join('.') : window.location.host;

    return `${protocol}//${props.tenant.subdomain}.${baseHost}/`;
});
</script>

<template>
    <Head title="Central Dashboard - Nexura" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-slate-100 flex items-center justify-center p-6 relative overflow-hidden font-sans">
        <!-- Floating decorative blobs -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse"></div>

        <div class="max-w-3xl w-full bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 md:p-12 shadow-2xl z-10 relative">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-slate-850 pb-8 mb-8">
                <div>
                    <span class="text-indigo-400 text-xs font-semibold uppercase tracking-widest bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-500/20">Central Plane</span>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mt-2 bg-gradient-to-r from-white via-slate-200 to-indigo-300 bg-clip-text text-transparent">
                        Nexura Engine
                    </h1>
                    <p class="text-slate-400 text-sm mt-1">Hello, {{ user?.name }} &mdash; manage your account settings and tenant workspaces.</p>
                </div>

                <div class="flex gap-3">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border border-slate-700 hover:border-slate-600 focus:outline-none"
                    >
                        Sign Out
                    </Link>
                </div>
            </div>

            <!-- Workspace Status Cards -->
            <div class="grid gap-6">
                <div v-if="tenant" class="bg-gradient-to-r from-indigo-950/40 to-slate-900/40 border border-indigo-500/20 rounded-2xl p-6 md:p-8 hover:border-indigo-500/40 transition-all duration-300">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                                Workspace Active
                            </h2>
                            <p class="text-slate-400 text-xs mt-1 uppercase tracking-wider font-semibold">Subdomain Identifier</p>
                            <code class="text-indigo-300 font-mono text-sm bg-slate-950/80 px-2 py-1 rounded mt-1.5 inline-block border border-slate-850">
                                {{ tenant.subdomain }}.domain.localhost
                            </code>
                        </div>
                    </div>

                    <p class="text-slate-300 text-sm mt-6 mb-8 leading-relaxed">
                        Your multi-tenant canvas editor is ready. You can modify pages and preview the published layouts dynamically on your dedicated sandbox subdomain.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a
                            :href="tenantEditorUrl"
                            class="flex-1 text-center bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/25 focus:outline-none"
                        >
                            Open Canvas Editor
                        </a>
                        <a
                            :href="tenantPublicUrl"
                            target="_blank"
                            class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold py-3 px-6 rounded-xl transition-all duration-200 border border-slate-700 hover:border-slate-600 focus:outline-none flex items-center justify-center gap-1.5"
                        >
                            View Live Site
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div v-else class="bg-red-950/20 border border-red-500/20 rounded-2xl p-6 md:p-8">
                    <h2 class="text-xl font-bold text-red-400">Workspace Missing</h2>
                    <p class="text-slate-300 text-sm mt-2">
                        We could not detect an active tenant workspace associated with your user account. Please contact support or re-register.
                    </p>
                </div>
            </div>

            <!-- Footer Details -->
            <div class="mt-8 text-center text-xs text-slate-500">
                Single-Database multi-tenancy enabled. Cookies scoped to <span class="text-indigo-400">.domain.localhost</span>.
            </div>
        </div>
    </div>
</template>
