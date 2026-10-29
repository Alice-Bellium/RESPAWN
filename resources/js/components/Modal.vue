<script setup>
import {nextTick, onMounted, onBeforeUnmount, ref} from 'vue';
import {X} from '@lucide/vue';
defineProps({title: String});
const emit = defineEmits(['close']);
const panel = ref(null);
let previous;
let previousOverflow;
function keydown(event) {
    if (event.key === 'Escape') emit('close');
    if (event.key !== 'Tab') return;
    const targets = [...panel.value.querySelectorAll('button, input, select, textarea, a[href]')].filter(
        (node) => !node.disabled && node.offsetParent,
    );
    const first = targets[0];
    const last = targets.at(-1);
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last?.focus();
    }
    if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first?.focus();
    }
}
onMounted(async () => {
    previous = document.activeElement;
    previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    await nextTick();
    panel.value.focus();
    document.addEventListener('keydown', keydown);
});
onBeforeUnmount(() => {
    document.body.style.overflow = previousOverflow;
    document.removeEventListener('keydown', keydown);
    previous?.focus();
});
</script>
<template>
    <Teleport to="body">
        <div
            class="modal-backdrop"
            @click.self="$emit('close')"
        >
            <section
                ref="panel"
                class="modal-panel"
                role="dialog"
                aria-modal="true"
                :aria-label="title"
                tabindex="-1"
            >
                <header class="modal-header">
                    <h2>{{ title }}</h2>
                    <button
                        class="icon-button"
                        aria-label="Закрыть"
                        @click="$emit('close')"
                    >
                        <X />
                    </button>
                </header>
                <div class="modal-body"><slot /></div>
            </section>
        </div>
    </Teleport>
</template>
