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
        <span v-if="modelValue.length" class="clear-btn" @click="$emit('clear')">×</span>
    </div>
</template>

<script>
export default {
    name: 'SearchInput',
    props: ['modelValue'],
};
</script>
