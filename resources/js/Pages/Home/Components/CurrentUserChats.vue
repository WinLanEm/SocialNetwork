<template>
    <ul class="current-user-chats">
        <li
            v-for="chat in chats"
            :key="chat.id"
            class="current-user"
            @mousedown.prevent
            @click="$emit('chatSelected', chat.recipient.data)"
        >
            <div class="user-data">
                <div class="fake-user-avatar">{{ chat.recipient.data.username[0] }}</div>
                <div class="name_and_last_message">
                    <div class="username_and_circle">
                        <h3 class="username">{{ chat.recipient.data.username }}</h3>
                        <div
                            class="blue-circle blue-circle-in-channel"
                            :style="{ display: chat.chat_data.is_read ? 'none' : 'block' }"
                        ></div>
                    </div>
                    <h3 class="last-message">{{ chat.chat_data.last_message }}</h3>
                </div>
            </div>
            <div class="user-last-seen">{{ formatDataToUser(chat.chat_data.updated_at) }}</div>
            <input type="hidden" class="chat-id" :value="chat.chat_data.chat_id" />
        </li>
    </ul>
</template>

<script>
export default {
    props: ['chats'],
    emits: ['chatSelected'],
    methods: {
        formatDataToUser(dateStr) {
            let iso = dateStr.replace(' ', 'T');
            iso = iso.replace(/(\.\d{3})\d+/, '$1');
            if (!iso.endsWith('Z')) iso += 'Z';
            const date = new Date(iso);
            if (isNaN(date)) return 'Invalid Date';
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
    },
};
</script>
