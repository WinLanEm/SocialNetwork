<template>
    <div class="current-user-chats-wrapper">
        <ul class="current-user-chats">
            <li v-for="chat in internalChats" :key="chat.id" class="current-user" @mousedown.prevent @click="$emit('show-chat', chat.recipient.data)">
                <div class="user-data">
                    <img class="fake-user-avatar" v-if="chat.recipient.data.avatar_url" :src="chat.recipient.data.avatar_url" :alt="chat.recipient.data.avatar_url"/>
                    <div v-else class="fake-user-avatar">{{ chat.recipient.data.username[0] }}</div>
                    <div class="name_and_last_message">
                        <div class="username_and_circle">
                            <h3 class="username">{{ chat.recipient.data.username }}</h3>
                            <div
                                class="blue-circle blue-circle-in-channel"
                                :style="{ display: chat.chat_data.is_read || user_id === chat.recipient.data.id ? 'none' : 'block' }"
                            ></div>
                        </div>
                        <h3 class="last-message">{{ chat.chat_data.last_message }}</h3>
                    </div>
                </div>
                <div class="user-last-seen">
                    {{ formatDataToUser(chat.chat_data.updated_at) }}
                </div>
                <input type="hidden" class="chat-id" :value="chat.chat_data.chat_id">
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        internalChats: Array,
        user_id: Number,
        formatDataToUser: {
            type: Function,
            required: true
        }
    },
    emits: ['show-chat']
};
</script>
