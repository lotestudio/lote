<style>
.editable-content {
    display: inline-block;
    min-width: 20px;
    min-height: 20px;
    border-bottom: 1px dotted red;
    outline: none;
}
</style>

<template>
    <div
        class="editable-content focus:border-neopharm-500"
        contenteditable="true"
        @blur="onEditorBlur"
        @focus="onEditorFocus"
        @keyup="onEditorChange"
        @paste="onEditorChange"
        @click="makeEditable"
        ref="textEditor"
    >
        <slot></slot>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { nextTick, onMounted, ref } from 'vue';

const emit = defineEmits([
    'on_editor_save',
    'on_editor_save_error',
    'on_editor_blur',
    'on_editor_change',
    'on_editor_focus',
]);

const props = defineProps({
    id: Number,
    column: String,
    model: String,
    locale: String,
    data: {
        Object,
        default: () => ({}),
    },
    method: {
        type: String,
        default: 'post',
    },
    emit_response: {
        type: Boolean,
        default: false,
    },
    post_url: {
        type: String,
        default: '/admin/save_editable',
    },
});

const changed = ref(false);
const isSaving = ref(false);

// пазим "последно записаното/известно" съдържание
const initialValue = ref<string>('');

// типизираме ref-а, за да няма never/null проблеми
const textEditor = ref<HTMLDivElement | null>(null);

const normalize = (value: string) => value.trim();

const getValue = (): string => {
    const el = textEditor.value;
    if (!el) return '';
    return normalize(el.textContent ?? '');
};

onMounted(() => {
    initialValue.value = getValue();
    changed.value = false;
});

const validate = () => {
    return true;
    // return getValue() !== '';
};

const save = async () => {
    if (isSaving.value) return;
    if (!validate()) {
        // връщаме старата стойност при невалидно
        if (textEditor.value) textEditor.value.textContent = initialValue.value;
        changed.value = false;
        return;
    }

    const valueToSave = getValue();

    // ключовото: ако няма промяна, НЕ пращаме request
    if (valueToSave === initialValue.value) {
        changed.value = false;
        return;
    }

    isSaving.value = true;

    const payload = {
        model: props.model,
        id: props.id,
        value: valueToSave,
        column: props.column,
        locale: props.locale,
        _method: props.method,
        ...props.data,
    };

    try {
        const response = await axios.post(props.post_url, payload);

        if (textEditor.value) textEditor.value.textContent = valueToSave;
        initialValue.value = valueToSave;
        changed.value = false;

        emit('on_editor_save', props.emit_response ? response.data : valueToSave);
    } catch (e: unknown) {
        emit('on_editor_save_error');
        if (textEditor.value) textEditor.value.textContent = initialValue.value;
        changed.value = false;
    } finally {
        isSaving.value = false;
    }
};

// event handlers
const onEditorBlur = () => {
    emit('on_editor_blur');

    // по желание: ако не е changed, не правим нищо
    if (!changed.value) return;

    void save();
};

const onEditorChange = () => {
    emit('on_editor_change');
    changed.value = getValue() !== initialValue.value;
};

const onEditorFocus = () => {
    emit('on_editor_focus');

    // снимаме стойността при фокус, за да сравняваме спрямо нея
    initialValue.value = getValue();
    changed.value = false;
};

const makeEditable = () => {
    nextTick(() => {
        textEditor.value?.focus();
    });
};
</script>
