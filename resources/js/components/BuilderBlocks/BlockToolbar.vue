<script setup>
import { inject, ref } from 'vue';
import {
    Trash2,
    Copy,
    ClipboardPaste,
    ChevronUp,
    ChevronDown,
    SquareUserRound,
    ArrowUpFromLine,
} from '@lucide/vue';

const props = defineProps({
    nodeId: { type: String, required: true },
    visible: { type: Boolean, default: false },
});

const blockActions = inject('blockActions', null);

const showConfirm = ref(false);
const confirmDelete = () => {
    if (showConfirm.value) {
        blockActions?.deleteBlockById(props.nodeId);
        showConfirm.value = false;
    } else {
        showConfirm.value = true;
        setTimeout(() => {
            showConfirm.value = false;
        }, 3000);
    }
};

const handleDuplicate = () => blockActions?.duplicateBlock(props.nodeId);
const handleMoveUp = () => blockActions?.moveBlock(props.nodeId, 'up');
const handleMoveDown = () => blockActions?.moveBlock(props.nodeId, 'down');
const handleCopy = () => blockActions?.copyBlock(props.nodeId);
const handlePaste = () => blockActions?.pasteBlock(props.nodeId);
const handleWrap = () => blockActions?.wrapInContainer(props.nodeId);
</script>

<template>
    <div
        class="block-toolbar absolute top-2 right-2 z-20 flex items-center gap-0.5 border border-slate-700/50 bg-slate-900/90 p-0.5 shadow-lg backdrop-blur-sm transition-opacity"
        :class="
            visible
                ? 'pointer-events-auto opacity-100'
                : 'pointer-events-none opacity-0'
        "
        @click.stop
    >
        <button
            v-if="blockActions"
            @click="handleMoveUp"
            title="Move up"
            class="cursor-pointer rounded border-0 bg-transparent p-1 text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white"
        >
            <ChevronUp class="h-3.5 w-3.5" />
        </button>
        <button
            v-if="blockActions"
            @click="handleMoveDown"
            title="Move down"
            class="cursor-pointer rounded border-0 bg-transparent p-1 text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white"
        >
            <ChevronDown class="h-3.5 w-3.5" />
        </button>
        <div class="mx-0.5 h-4 w-px bg-slate-700/50"></div>
        <button
            v-if="blockActions"
            @click="handleDuplicate"
            title="Duplicate"
            class="cursor-pointer rounded border-0 bg-transparent p-1 text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white"
        >
            <Copy class="h-3.5 w-3.5" />
        </button>
        <button
            v-if="blockActions"
            @click="handleCopy"
            title="Copy"
            class="cursor-pointer rounded border-0 bg-transparent p-1 text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white"
        >
            <ArrowUpFromLine class="h-3.5 w-3.5" />
        </button>
        <button
            v-if="blockActions && blockActions.copiedBlock?.value"
            @click="handlePaste"
            title="Paste"
            class="cursor-pointer rounded border-0 bg-transparent p-1 text-emerald-400 transition-colors hover:bg-emerald-700/30 hover:text-emerald-300"
        >
            <ClipboardPaste class="h-3.5 w-3.5" />
        </button>
        <div class="mx-0.5 h-4 w-px bg-slate-700/50"></div>
        <button
            v-if="blockActions"
            @click="handleWrap"
            title="Wrap in Column"
            class="cursor-pointer rounded border-0 bg-transparent p-1 text-slate-300 transition-colors hover:bg-slate-700/60 hover:text-white"
        >
            <SquareUserRound class="h-3.5 w-3.5" />
        </button>
        <button
            v-if="blockActions"
            @click="confirmDelete"
            :title="showConfirm ? 'Click again to confirm' : 'Delete'"
            class="cursor-pointer rounded border-0 bg-transparent p-1 transition-colors"
            :class="
                showConfirm
                    ? 'bg-rose-600/30 text-rose-300'
                    : 'text-slate-300 hover:bg-rose-600/20 hover:text-rose-400'
            "
        >
            <Trash2 class="h-3.5 w-3.5" />
        </button>
    </div>
</template>

<style scoped>
.block-toolbar {
    background: rgb(15 15 16 / 94%);
    border-color: #3f4652;
    border-radius: 4px;
}

.block-toolbar button {
    border-radius: 3px;
}

.block-toolbar button:focus-visible {
    outline: 2px solid #93c5fd;
    outline-offset: 1px;
}
</style>
