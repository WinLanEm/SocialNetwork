<template>
    <div class="messages-container" :class="{'inactive-message-container': !currentChatId}">
        <h3 v-if="!currentChatId">Select a chat to start messaging</h3>
        <!-- Добавляем ref для доступа к DOM-элементу -->
        <ul v-else class="messages" ref="messagesContainer">
            <li v-if="isLoadingOlderMessages" class="loading-indicator">
                Loading older messages...
            </li>
            <!-- Отображаем локальный массив сообщений -->
            <li v-for="message in localMessages" :key="message.id"
                :data-message-id="message.id"
                :class="message.sender_id === user_id ? 'sender-message-container' : 'receiver-message-container'"
                :id="message.is_read ? 'readed' : 'unreaded'">
                <div class="message-content-wrapper">
                    <p class="message-content">{{ message.content }}</p>
                    <div class="other-message-info-wrapper">
                        <p class='message-data'>{{ formatDataToUser(message.updated_at) }}</p>
                        <div v-if="!message.is_read && message.sender_id === user_id" class="blue-circle"></div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';

export default {
    props: {
        currentChatId: [Number, String, null],
        user_id: Number,
        formatDataToUser: {
            type: Function,
            required: true
        },
        newMessage: Object
    },
    emits: ['update-last-message', 'messages-read', 'message-id-updated'],
    setup(props, { emit }) {
        const localMessages = ref([]);
        const isLoadingOlderMessages = ref(false);
        const hasMoreMessages = ref(true);
        const page = ref(1);
        const observer = ref(null);
        const messagesContainer = ref(null);
        const csrfToken = ref(document.querySelector('meta[name="csrf-token"]')?.content || '');

        const getHeaders = () => ({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken.value,
            'X-Requested-With': 'XMLHttpRequest'
        });

        // --- ЛОГИКА ЗАГРУЗКИ СООБЩЕНИЙ ---
        const fetchMessages = async (chatId) => {
            if (!chatId) return;
            isLoadingOlderMessages.value = true;
            try {
                const response = await fetch(route('get-chat-messages', { chat_id: chatId, page: 1 }), { headers: getHeaders() });
                if (!response.ok) throw new Error('Failed to load messages');
                const data = await response.json();
                localMessages.value = data.messages.data.reverse(); // Порядок важен
                page.value = 1;
                hasMoreMessages.value = data.messages.next_page_url !== null;

                await nextTick();
                scrollToBottom();
                setupMessageVisibilityObserver();
                setupScrollListener();
            } catch (error) {
                console.error('Error fetching messages:', error);
            } finally {
                isLoadingOlderMessages.value = false;
            }
        };

        const loadOlderMessages = async () => {
            if (isLoadingOlderMessages.value || !hasMoreMessages.value) return;
            isLoadingOlderMessages.value = true;
            try {
                const nextPage = page.value + 1;
                const response = await fetch(route('get-chat-messages', { chat_id: props.currentChatId, page: nextPage }), { headers: getHeaders() });
                const data = await response.json();

                const oldScrollHeight = messagesContainer.value.scrollHeight;

                localMessages.value.unshift(...data.messages.data.reverse());
                page.value = nextPage;
                hasMoreMessages.value = data.messages.next_page_url !== null;

                await nextTick();
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight - oldScrollHeight;
            } catch (error) {
                console.error('Error loading older messages:', error);
            } finally {
                isLoadingOlderMessages.value = false;
            }
        };

        // --- ЛОГИКА ПРОКРУТКИ И ВИДИМОСТИ ---
        const scrollToBottom = () => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
            }
        };

        const setupScrollListener = () => {
            if (!messagesContainer.value) return;
            messagesContainer.value.onscroll = () => {
                if (messagesContainer.value.scrollTop < 100) {
                    loadOlderMessages();
                }
            };
        };

        const setupMessageVisibilityObserver = () => {
            if (observer.value) observer.value.disconnect();
            if (!messagesContainer.value) return;

            const visibleIdsBatch = new Set();
            let debounceTimer;

            observer.value = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const messageId = entry.target.dataset.messageId;
                    const isReceiverUnread = entry.target.classList.contains('receiver-message-container') && entry.target.id === 'unreaded';

                    if (entry.isIntersecting && isReceiverUnread) {
                        visibleIdsBatch.add(messageId);
                    }
                });

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(async () => {
                    if (visibleIdsBatch.size > 0) {
                        try {
                            const idsToMark = Array.from(visibleIdsBatch);
                            await fetch(route('mark-messages-as-read'), {
                                method: 'POST',
                                headers: getHeaders(),
                                body: JSON.stringify({ message_ids: idsToMark, chat_id: props.currentChatId, user_id: props.user_id })
                            });
                        } catch (error) {
                            console.error('Error marking messages as read:', error);
                        }
                        visibleIdsBatch.clear();
                    }
                }, 1000);
            }, { root: messagesContainer.value, threshold: 0.1, rootMargin:'100px' });

            messagesContainer.value.querySelectorAll('.receiver-message-container').forEach(el => observer.value.observe(el));
        };

        // --- ЛОГИКА WEBSOCKETS ---
        const subscribeToChatEvents = (newChatId, oldChatId) => {
            if (oldChatId) {
                window.Echo.leave(`message_in_chat.${oldChatId}`);
            }
            if (newChatId) {
                window.Echo.private(`message_in_chat.${newChatId}`)
                    .listen('.message.created', async (data) => { // <-- делаем функцию асинхронной
                        if (data.sender_id !== props.user_id) {
                            localMessages.value.push(data);
                            scrollToBottom();

                            // --- НАЧАЛО ИСПРАВЛЕНИЯ ---
                            // Отправляем запрос, чтобы пометить сообщение как прочитанное НЕМЕДЛЕННО
                            try {
                                await fetch(route('mark-messages-as-read'), {
                                    method: 'POST',
                                    headers: getHeaders(),
                                    body: JSON.stringify({
                                        message_ids: [data.id], // ID нового сообщения
                                        chat_id: newChatId,     // ID текущего чата
                                        user_id: props.user_id
                                    })
                                });
                            } catch (error) {
                                console.error('Failed to mark websocket message as read:', error);
                            }
                            // --- КОНЕЦ ИСПРАВЛЕНИЯ ---
                        }
                        // Это событие по-прежнему нужно для обновления last_message в списке чатов
                        emit('update-last-message', data);
                    });
            }
        };

        // --- СЛЕЖЕНИЕ ЗА ИЗМЕНЕНИЯМИ ---
        watch(() => props.currentChatId, (newChatId, oldChatId) => {
            localMessages.value = [];
            fetchMessages(newChatId);
            subscribeToChatEvents(newChatId, oldChatId);
        }, { immediate: true });

        // Добавляем новое отправленное сообщение в список
        watch(() => props.newMessage, (msg) => {
            if (msg && msg.id) {
                localMessages.value.push(msg);
                nextTick(() => scrollToBottom());
            }
        });

        // Глобальные слушатели, которые влияют на текущий чат
        onMounted(() => {
            window.Echo.private(`read_messages_user.${props.user_id}`)
                .listen('.messages.read', (data) => {
                    if (data.chat_id === props.currentChatId) {
                        localMessages.value = localMessages.value.map(message => {
                            if (data.message_ids.includes(message.id)) {
                                return { ...message, is_read: true };
                            }
                            return message;
                        });
                    }
                });

            window.Echo.private(`send_real_id.${props.user_id}`)
                .listen('.message_id.updated', (data) => {
                    const index = localMessages.value.findIndex(m => m.id === data.temp_id);
                    if (index !== -1) {
                        localMessages.value[index].id = data.message_id;
                    }
                });
        });

        onUnmounted(() => {
            if (observer.value) observer.value.disconnect();
            if (props.currentChatId) window.Echo.leave(`message_in_chat.${props.currentChatId}`);
        });

        return {
            localMessages,
            isLoadingOlderMessages,
            messagesContainer
        };
    }
};
</script>
