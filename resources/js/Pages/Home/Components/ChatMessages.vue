<template>
    <div class="messages-container" :class="{'inactive-message-container': !currentChatId}">
        <h3 v-if="!currentChatId">Select a chat to start messaging</h3>
        <ul class="messages">
            <li v-if="isLoadingOlderMessages" class="loading-indicator">
                Loading older messages...
            </li>
            <li v-for="message in messages" :key="message.id"
                :data-message-id="message.id"
                :class="message.sender_id === user_id ? 'sender-message-container' : 'receiver-message-container'"
                :id="message.is_read ? 'readed' : 'unreaded'">
                <div class="message-content-wrapper">
                    <p class="message-content">{{message.content}}</p>
                    <div class="other-message-info-wrapper">
                        <p class='message-data'>{{formatDataToUser(message.updated_at)}}</p>
                        <div v-if="!message.is_read && message.sender_id === user_id" class="blue-circle"></div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        currentChatId: [Number, String],
        messages: Array,
        user_id: Number,
        isLoadingOlderMessages: Boolean,
        formatDataToUser: Function
    }
};
</script>
