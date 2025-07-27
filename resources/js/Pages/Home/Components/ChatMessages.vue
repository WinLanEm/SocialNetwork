<template>
    <div class="messages-container" :class="{ 'inactive-message-container': !currentChatId }" ref="messagesContainer">
        <h3 v-if="!currentChatId">Select a chat to start messaging</h3>
        <ul class="messages">
            <li v-if="isLoadingOlderMessages" class="loading-indicator">
                Loading older messages...
            </li>
            <li
                v-for="message in messages"
                :key="message.id"
                :data-message-id="message.id"
                :class="message.sender_id === currentUserId ? 'sender-message-container' : 'receiver-message-container'"
                ref="messageRefs"
            >
                <div class="message-content-wrapper">
                    <p class="message-content">{{ message.content }}</p>
                    <div class="other-message-info-wrapper">
                        <p class="message-data">{{ formatDataToUser(message.updated_at) }}</p>
                        <div
                            v-if="!message.is_read && message.sender_id === currentUserId"
                            class="blue-circle"
                        ></div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';

export default {
    props: {
        currentChatId: [String, Number],
        currentUserId: Number,
        initialMessages: Array,
    },
    emits: ['load-older-messages', 'mark-messages-read'],
    setup(props, { emit }) {
        const messages = ref([...props.initialMessages]);
        const observer = ref(null);
        const isLoadingOlderMessages = ref(false);
        const messagesContainer = ref(null);
        const messageRefs = ref([]);

        function formatDataToUser(dateStr) {
            let iso = dateStr.replace(' ', 'T');
            iso = iso.replace(/(\.\d{3})\d+/, '$1');
            if (!iso.endsWith('Z')) iso += 'Z';
            const date = new Date(iso);
            if (isNaN(date)) return 'Invalid Date';
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        const visibleMessageIds = new Set();
        let debounceTimer = null;

        function setupObserver() {
            if (!messagesContainer.value) return;

            observer.value = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        const id = entry.target.dataset.messageId;
                        if (entry.isIntersecting && entry.target.className.includes('receiver-message-container')) {
                            visibleMessageIds.add(id);
                        } else {
                            visibleMessageIds.delete(id);
                        }
                    });

                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        if (visibleMessageIds.size > 0) {
                            emit('mark-messages-read', Array.from(visibleMessageIds));
                            visibleMessageIds.clear();
                        }
                    }, 1000);
                },
                {
                    root: messagesContainer.value,
                    rootMargin: '100px',
                    threshold: 0.1,
                }
            );

            nextTick(() => {
                messageRefs.value.forEach((el) => {
                    observer.value.observe(el);
                });
            });
        }

        function handleScroll() {
            if (!messagesContainer.value) return;
            if (
                messagesContainer.value.scrollTop === 0 &&
                !isLoadingOlderMessages.value
            ) {
                emit('load-older-messages');
            }
        }

        watch(() => props.initialMessages, (newMessages) => {
            messages.value = [...newMessages];
            nextTick(() => {
                setupObserver();
                if (messagesContainer.value)
                    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
            });
        });

        onMounted(() => {
            setupObserver();
            if (messagesContainer.value) {
                messagesContainer.value.addEventListener('scroll', handleScroll);
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
            }
        });

        onBeforeUnmount(() => {
            if (observer.value) observer.value.disconnect();
            if (messagesContainer.value)
                messagesContainer.value.removeEventListener('scroll', handleScroll);
        });

        return {
            messages,
            isLoadingOlderMessages,
            messagesContainer,
            formatDataToUser,
            messageRefs,
        };
    },
};
</script>
