<template>
    <div class="search-wrapper">
        <input
            type="text"
            placeholder="Search"
            class="search-members"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />
        <span
            class="clear-btn"
            v-if="modelValue.length > 0"
            @click="$emit('clear')"
        >×</span>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: String,
        isFocused: Boolean
    },
    emits: ['update:modelValue', 'focus', 'blur', 'clear']
};
</script>
