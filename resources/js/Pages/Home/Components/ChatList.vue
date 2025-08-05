<template>
    <div class="members" :class="{'members-active': isInputFocused}">
        <SearchResultsList
            v-if="isInputFocused"
            :users="users"
            :formatDataToUser="formatDataToUser"
            @show-chat="$emit('show-chat', $event)"
        />
        <ActiveChatsList
            v-else
            :internalChats="internalChats"
            :user_id="user_id"
            :formatDataToUser="formatDataToUser"
            @show-chat="$emit('show-chat', $event)"
        />
    </div>
</template>

<script>
import SearchResultsList from './SearchResultsList.vue';
import ActiveChatsList from './ActiveChatsList.vue';

export default {
    components: {
        SearchResultsList,
        ActiveChatsList
    },
    props: {
        isInputFocused: Boolean,
        users: Array,
        internalChats: Array,
        user_id: Number,
        formatDataToUser: Function
    },
    emits: ['show-chat']
};
</script>
