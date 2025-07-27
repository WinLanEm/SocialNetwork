<template>
    <Head :title="title" />
    <div class="home-container">
        <ChatSidebar
            :chats="chats"
            :user-id="user_id"
            @show-chat="handleShowChat"
        />

        <div class="concrete-chat-wrapper">
            <input type="hidden" class="current_chat_id" v-model="currentChatId" />

            <div class="chat-partner-wrapper" :class="{ 'inactive-chat-partner-wrapper': !currentChatId }">
                <div class="chat-partner-data" v-html="partnerHtml"></div>
                <div class="chat-partner-buttons"></div>
            </div>

            <ChatMessages
                :currentChatId="currentChatId"
                :currentUserId="user_id"
                :initialMessages="messages"
                @load-older-messages="loadOlderMessages"
                @mark-messages-read="markMessagesAsRead"
            />

            <MessageInput
                v-model="messageContent"
                :currentChatId="currentChatId"
                @send="sendMessage"
            />
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

import ChatSidebar from './Components/ChatSidebar.vue';
import ChatMessages from './Components/ChatMessages.vue';
import MessageInput from './Components/MessageInput.vue';

export default {
    components: { Head, ChatSidebar, ChatMessages, MessageInput },
    props: {
        title: String,
        user_id: Number,
        chats: Array,
    },
    setup(props) {
        const currentChatId = ref(null);
        const messageContent = ref('');
        const partnerHtml = ref('');
        const messages = ref([]);
        const isLoadingOlderMessages = ref(false);
        const page = ref(1);
        const hasMoreMessages = ref(true);

        // CSRF token helper
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        function getHeaders() {
            return {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            };
        }

        // Show chat - load or create chat
        async function handleShowChat(user) {
            currentChatId.value = null;
            messageContent.value = '';
            partnerHtml.value = `
        <div class="concrete_user_name_and_last_seen_wrapper">
          <h2>${user.username}</h2>
          <h6>${user.last_seen || '22.06.2024'}</h6>
        </div>
        ${
                user.avatar_url
                    ? `<img src="${user.avatar_url}" alt="${user.username}"/>`
                    : `<div class="fake-avatar concrete_fake_avatar">${user.username[0]}</div>`
            }
      `;

            try {
                const res = await fetch(route('get-or-create-chat'), {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        participants: [user.id],
                        type: 'private',
                    }),
                    credentials: 'include',
                });

                if (!res.ok) throw new Error('Failed to get or create chat');
                const chatData = await res.json();

                messages.value = (chatData.messages?.data || []).sort(
                    (a, b) => new Date(a.updated_at) - new Date(b.updated_at)
                );
                currentChatId.value = chatData.chat_id;
                page.value = 1;
                hasMoreMessages.value = true;
            } catch (err) {
                alert(err.message || 'Error loading chat');
            }
        }

        async function loadOlderMessages() {
            if (!currentChatId.value || isLoadingOlderMessages.value || !hasMoreMessages.value) return;
            isLoadingOlderMessages.value = true;

            try {
                const res = await fetch(
                    route('get-chat-messages', {
                        chat_id: currentChatId.value,
                        page: page.value + 1,
                    }),
                    {
                        headers: getHeaders(),
                    }
                );
                if (!res.ok) throw new Error('Failed to load older messages');
                const data = await res.json();

                if (!data.messages?.data.length) {
                    hasMoreMessages.value = false;
                    return;
                }

                messages.value = [
                    ...data.messages.data.sort((a, b) => new Date(a.updated_at) - new Date(b.updated_at)),
                    ...messages.value,
                ];

                page.value++;
            } catch (e) {
                console.error('Error loading older messages:', e);
            } finally {
                isLoadingOlderMessages.value = false;
            }
        }

        async function markMessagesAsRead(messageIds) {
            if (!currentChatId.value || !messageIds.length) return;
            try {
                await fetch(route('mark-messages-as-read'), {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        message_ids: messageIds,
                        chat_id: currentChatId.value,
                        user_id: props.user_id,
                    }),
                });
            } catch (e) {
                console.error('Error marking messages as read:', e);
            }
        }

        async function sendMessage() {
            if (!messageContent.value.trim() || !currentChatId.value) return;

            const tempId = 'temp-' + crypto.randomUUID();
            const content = messageContent.value;
            messages.value.push({
                id: tempId,
                content,
                sender_id: props.user_id,
                updated_at: new Date().toISOString(),
                is_read: false,
            });

            messageContent.value = '';

            try {
                const res = await fetch(route('send-message'), {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        chat_id: currentChatId.value,
                        content,
                        temp_id: tempId,
                    }),
                    credentials: 'include',
                });
                if (!res.ok) throw new Error('Failed to send message');
                // Optionally update message id here on success via events
            } catch (err) {
                alert('Error sending message');
            }
        }

        return {
            currentChatId,
            messageContent,
            partnerHtml,
            messages,
            isLoadingOlderMessages,
            handleShowChat,
            loadOlderMessages,
            markMessagesAsRead,
            sendMessage,
        };
    },
};
</script>

<style src="../../../css/Home/home.css"></style>
