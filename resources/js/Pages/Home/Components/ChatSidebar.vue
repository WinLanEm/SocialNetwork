<template>
    <div class="chats" :class="{ 'sidebar-active': isInputFocused }">
        <div class="other_and_search">
            <ProfileButton />
            <SearchInput
                v-model="searchQuery"
                @focus="isInputFocused = true"
                @blur="isInputFocused = false"
                @clear="searchQuery = ''"
            />
        </div>

        <div class="members" :class="{ 'members-active': isInputFocused }">
            <template v-if="isInputFocused">
                <div class="users_not_found" v-if="!users.length">No users found</div>
                <UserList :users="users" @userSelected="showChat" />
            </template>
            <template v-else>
                <CurrentUserChats :chats="chats" :currentUserId="userId" @chatSelected="showChat" />
            </template>
        </div>
    </div>
</template>

<script>
import { ref, watch } from 'vue';
import ProfileButton from './ProfileButton.vue';
import SearchInput from './SearchInput.vue';
import UserList from './UserList.vue';
import CurrentUserChats from './CurrentUserChats.vue';

export default {
    components: {
        ProfileButton,
        SearchInput,
        UserList,
        CurrentUserChats,
    },
    props: {
        chats: Array,
        userId: Number,
    },
    emits: ['show-chat'],
    setup(props, { emit }) {
        const searchQuery = ref('');
        const users = ref([]);
        const loading = ref(false);
        const isInputFocused = ref(false);

        let debounceTimeout = null;

        watch(searchQuery, (val) => {
            clearTimeout(debounceTimeout);
            if (!val.trim()) {
                users.value = [];
                return;
            }
            debounceTimeout = setTimeout(async () => {
                loading.value = true;
                try {
                    const res = await fetch(route('search-users', { username: val.trim() }), {
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                        },
                    });
                    users.value = await res.json();
                } catch {
                    users.value = [];
                } finally {
                    loading.value = false;
                }
            }, 300);
        });

        function showChat(user) {
            emit('show-chat', user);
        }

        return {
            searchQuery,
            users,
            loading,
            isInputFocused,
            showChat,
        };
    },
};
</script>
