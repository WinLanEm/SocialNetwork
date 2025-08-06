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
                v-if="searchQuery.length > 0 && !loading"
                @click="clearSearch"
            >×</span>
        </div>
    </div>
</template>

<script>
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';

export default {
    props: {},
    emits: ['toggle-sidebar', 'input-focus', 'results', 'loading'],
    setup(props, { emit }) {
        const searchQuery = ref('');
        const loading = ref(false);
        const csrfToken = ref(document.querySelector('meta[name="csrf-token"]')?.content || '');

        const clearSearch = () => {
            searchQuery.value = '';
        };

        watch(searchQuery, debounce((newVal) => {
            if (newVal.trim().length === 0) {
                emit('results', []);
                return;
            }

            loading.value = true;
            emit('loading', true);
            fetch(route('search-users', { username: newVal }), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.value,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Authorization': `Bearer ${localStorage.getItem('sanctum_token')}`,
                },
                credentials:"include"
            })
                .then(res => res.json())
                .then(data => {
                    emit('results', data);
                })
                .catch(() => {
                    emit('results', []);
                })
                .finally(() => {
                    loading.value = false;
                    emit('loading', false);
                });
        }, 300));

        return {
            searchQuery,
            loading,
            clearSearch
        };
    }
};
</script>
