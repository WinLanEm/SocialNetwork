<template>
    <div class="other_and_search">
        <div class="other-btn">
            <button class="profile-button" @click="$emit('toggle-sidebar')">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        <div class="search-wrapper">
            <input type="text"
                   placeholder="Search"
                   class="search-members"
                   v-model="searchQuery"
                   @focus="$emit('input-focus', true)"
                   @blur="$emit('input-focus', false)"
            />
            <span
                class="clear-btn"
                v-if="searchQuery.length > 0"
                @click="searchQuery = ''"
            >×</span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: String
    },
    emits: ['update:modelValue', 'toggle-sidebar', 'input-focus'],
    computed: {
        searchQuery: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    }
};
</script>
