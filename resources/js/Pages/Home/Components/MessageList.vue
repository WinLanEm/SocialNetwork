<template>
    <div class="messages-container" :class="{'inactive-message-container': !currentChatId}">
        <h3 v-if="!currentChatId">Select a chat to start messaging</h3>
        <ul class="messages" ref="messagesContainer">
            <li v-if="isLoadingOlderMessages" class="loading-indicator">
                Loading older messages...
            </li>
            <li
                v-for="message in messages"
                :key="message.id"
                :data-message-id="message.id"
                :class="message.sender_id === user_id ? 'sender-message-container' : 'receiver-message-container'"
                :id="message.is_read ? 'readed' : 'unreaded'"
                ref="messageElements"
            >
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
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { formatDataToUser } from '../Utils/dateFormatter';

export default {
    props: {
        messages: Array,
        currentChatId: [Number, String],
        isLoadingOlderMessages: Boolean,
        user_id: Number
    },
    emits: ['load-more'],
    setup(props, { emit }) {
        const messagesContainer = ref(null);
        const messageElements = ref([]);

        const setupScrollListener = () => {
            if (!messagesContainer.value) return;

            messagesContainer.value.addEventListener('scroll', () => {
                if (messagesContainer.value.scrollTop < 1000 &&
                    !props.isLoadingOlderMessages) {
                    emit('load-more');
                }
            });
        };

        onMounted(() => {
            nextTick(() => {
                setupScrollListener();
                scrollToBottom();
            });
        });

        const scrollToBottom = () => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
            }
        };

        return {
            messagesContainer,
            messageElements,
            formatDataToUser,
            scrollToBottom
        };
    }
};
</script>
