<template>
    <Head :title="title"/>
    <div class="home-container">
        <div class="chats" :class="{'sidebar-active':isInputFocused}">
            <div class="other_and_search">
                <div class="other-btn">
                    <button class="profile-button">
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
                           @focus="isInputFocused = true"
                           @blur="isInputFocused = false"
                    />
                    <span
                        class="clear-btn"
                        v-if="searchQuery.length > 0"
                        @click="searchQuery = ''"
                    >×</span>
                </div>
            </div>
            <div class="members" :class="{'members-active':isInputFocused}">
                <div v-if="isInputFocused">
                    <div class="users_not_found" v-if="users.length === 0">No users found</div>
                    <ul class="members-searched">
                        <li v-for="user in users" :key="user.id" @mousedown.prevent @click="showChat(user)">
                            <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.username"/>
                            <div class="fake-avatar" v-else>{{user.username[0]}}</div>
                            <div class="name_and_last_seen">
                                <h3>{{user.username}}</h3>
                                <h6 v-if="user.last_seen">{{user.last_seen}}</h6>
                            </div>
                        </li>
                    </ul>
                </div>
                <div v-else class="current-user-chats-wrapper">
                    <ul class="current-user-chats">
                        <li v-for="chat in chats" :key="chat.id" class="current-user" @mousedown.prevent @click="showChat(chat.recipient.data)">
                            <div class="user-data">
                                <div class="fake-user-avatar">{{chat.recipient.data.username[0]}}</div>
                                <div class="name_and_last_message">
                                    <div class="username_and_circle">
                                        <h3 class="username">{{ chat.recipient.data.username }}</h3>
                                        <div
                                            class="blue-circle blue-circle-in-channel"
                                            :style="{ display: chat.chat_data.is_read ? 'none' : 'block' }"
                                        ></div>
                                    </div>
                                    <h3 class="last-message">{{chat.chat_data.last_message}}</h3>
                                </div>
                            </div>
                            <div class="user-last-seen">
                                {{formatDataToUser(chat.chat_data.updated_at)}}
                            </div>
                            <input type="hidden" class="chat-id" :value=chat.chat_data.chat_id>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="concrete-chat-wrapper">
            <input type="hidden" class="current_chat_id" v-model="currentChatId">
            <div class="chat-partner-wrapper" :class="{'inactive-chat-partner-wrapper': !currentChatId}">
                <div class="chat-partner-data" ref="partnerData"></div>
                <div class="chat-partner-buttons"></div>
            </div>
            <div class="messages-container" :class="{'inactive-message-container': !currentChatId}">
                <h3 v-if="!currentChatId">Select a chat to start messaging</h3>
                <ul class="messages">
                    <li v-if="isLoadingOlderMessages" class="loading-indicator">
                        Loading older messages...
                    </li>
                </ul>
            </div>
            <div class="message-wrapper" :class="{'inactive-message-wrapper': !currentChatId}">
                <input class="message-write-input"
                       type="text"
                       name="message"
                       placeholder="Write a message..."
                       v-model="messageContent"
                       @keyup.enter="sendMessage"
                       :disabled="!currentChatId"
                />
                <span
                    class="message-input-btn"
                    v-if="messageContent.length > 0 && currentChatId"
                    @click="sendMessage"
                >
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 4L3 11L9 13L15 21L17 15L21 4Z" fill="#2A5885" stroke="#2A5885" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M3 11L21 4L9 13" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M15 21L17 15L9 13" stroke="#2A5885" stroke-width="1.5" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import {Head} from "@inertiajs/vue3";
import {ref, watch, onMounted, onUnmounted} from "vue";

export default {
    components: {
        Head
    },
    props: {
        title: String,
        user_id: Number,
        chats: Array,
    },
    setup(props) {
        const currentChatLastMessage = ref('');
        const currentChatId = ref(null);
        const messageContent = ref('');
        const searchQuery = ref('');
        const users = ref([]);
        const loading = ref(false);
        const isInputFocused = ref(false);
        const isChatLoading = ref(false);
        const partnerData = ref(null);
        let debounceTimeout = null;
        const visibleMessageIds = ref(new Set());
        const observer = ref(null);
        const csrfToken = ref('');
        const isLoadingOlderMessages = ref(false);
        const hasMoreMessages = ref(true);
        const page = ref(1);
        let debounceTimer;
        let visibleIdsBatch = new Set();
        watch(currentChatId, (newChatId, oldChatId) => {
            if(newChatId) {
                // Отписываемся от старого канала
                if(oldChatId) {
                    window.Echo.leave(`message_in_chat.${oldChatId}`);
                }

                window.Echo.private(`message_in_chat.${newChatId}`)
                    .listen('.message.created', data => {
                        if(data.content && data.sender_id !== props.user_id){
                            const messageContainer = document.querySelector('.messages');
                            if (!messageContainer) {
                                console.error('Messages container not found!');
                                return;
                            }
                            const message = document.createElement('div')
                            message.className = 'receiver-message-container'
                            message.innerHTML = `
                                <div class="message-content-wrapper">
                                    <p class="message-content">${data.content}</p>
                                     <div class="other-message-info-wrapper">
                                        <p class='message-data'>${formatDataToUser(data.data)}</p>
                                     </div>
                                </div>
                            `
                            messageContainer.appendChild(message)
                        }
                    });
            }
        }, {immediate: true});

        onMounted(() => {
            csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            setupMessageVisibilityObserver();
            window.Echo.private(`chat_render_by_user.${Number(props.user_id)}`)
                .listen('.chat.render', data => {
                    const userChats = document.querySelector('.current-user-chats')
                    const li = document.createElement('li')
                    console.log(data)
                    li.className = 'current-user'
                    li.innerHTML = `
                            <div class="user-data">
                                <div class="fake-user-avatar">${data.sender.username[0]}</div>
                                <div class="name_and_last_message">
                                    <div class="username_and_circle">
                                        <h3 class="username">${ data.sender.username }</h3>
                                        <div class="blue-circle blue-circle-in-channel"
                                            style="${data.is_read ? 'display: none;' : 'display: block;'}">
                                        </div>
                                    </div>
                                    <h3 class="last-message">${data.last_message}</h3>
                                </div>
                            </div>
                            <div class="user-last-seen">
                                ${formatDataToUser(data.data)}
                            </div>
                            <input type="hidden" class="chat-id" value="${data.chat_id}">
                    `
                    userChats.prepend(li)
                    li.addEventListener('click',() => {
                        showChat(data.sender)
                    })
                });
            window.Echo.private(`last_message.${props.user_id}`)
                .listen('.last_message.updated',data => {

                    const inputs = document.querySelectorAll('input.chat-id');
                    const input = Array.from(inputs).find(i => i.value === data.chat_id);

                    if (!input) {
                        console.warn('Чат не найден:', data.chat_id);
                        return;
                    }

                    const parent = input.closest('.current-user');
                    if (!parent) return;
                    if(currentChatId.value !== data.chat_id){
                        const readCircle = document.querySelector('.blue-circle-in-channel')
                        readCircle.style.display = 'block';
                    }

                    const lastMessage = parent.querySelector('.last-message');
                    if (lastMessage) {
                        lastMessage.textContent = data.content;
                    }
                })
            window.Echo.private(`fail_store_message_user.${props.user_id}`)
                .listen('.message.failed',data => {
                    alert(data.error)
                })
            window.Echo.private(`read_messages_user.${props.user_id}`)
                .listen('.messages.read',data => {
                    console.log(data)
                    if(currentChatId.value === data.chat_id){
                            data.message_ids.forEach((id) => {
                                const messageContainer = document.querySelector('.messages')
                                const messages = messageContainer.querySelectorAll(
                                    `.sender-message-container[data-message-id="${id}"]`
                                );
                                messages.forEach((message) => {
                                    const circle = message.querySelector('.blue-circle')
                                    if(!circle){
                                        return;
                                    }
                                    circle.style.display = 'none'
                                })
                        })
                    }
                })
            window.Echo.private(`make_chat_is_read_user.${props.user_id}`)
                .listen('.chat.is_read',data => {
                    const chats = document.querySelector('.current-user-chats')
                    const input = chats.querySelector(`.chat-id[value="${data.chat_id}"]`)
                    const div = input.closest('.current-user')
                    const circle = div.querySelector('.blue-circle-in-channel')
                    circle.style.display = 'none'
                })
            window.Echo.private(`send_real_id.${props.user_id}`)
                .listen('.message_id.updated',data => {
                    const tempElement = document.querySelector(`[data-message-id="${data.temp_id}"]`);
                    console.log('не обновился')
                    if (tempElement) {
                        tempElement.dataset.messageId = data.message_id;
                        console.log("обновился")
                    }
                })
        });
        onUnmounted(() => {
            if (observer.value) {
                observer.value.disconnect();
            }
        })
        function setupMessageVisibilityObserver() {
            const options = {
                root: document.querySelector('.messages'),
                rootMargin: '100px',
                threshold: 0.1
            };

            observer.value = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const messageId = entry.target.dataset.messageId;
                    if (entry.isIntersecting && entry.target.className === 'receiver-message-container') {
                        visibleMessageIds.value.add(messageId);
                        visibleIdsBatch.add(messageId);
                    } else {
                        visibleMessageIds.value.delete(messageId);
                        visibleIdsBatch.delete(messageId);
                    }
                });

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (visibleIdsBatch.size > 0) {
                        markMessagesAsRead();
                        visibleIdsBatch.clear();
                    }
                }, 1000);
            }, options);
        }
        async function markMessagesAsRead() {
            if (visibleMessageIds.value.size === 0 || !currentChatId.value) return;
            console.log(visibleMessageIds.value,currentChatId.value,props.user_id)
            try {
                await fetch(route('mark-messages-as-read'), {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        message_ids: Array.from(visibleMessageIds.value),
                        chat_id: currentChatId.value,
                        user_id: props.user_id
                    })
                });
            } catch (error) {
                console.error('Error marking messages as read:', error);
            }
        }
        function setupScrollListener() {
            const messagesContainer = document.querySelector('.messages');
            if (!messagesContainer) return;

            messagesContainer.addEventListener('scroll', async () => {
                if (messagesContainer.scrollTop === 0 &&
                    !isLoadingOlderMessages.value &&
                    hasMoreMessages.value) {
                    await loadOlderMessages();
                }
            });
        }
        async function loadOlderMessages() {
            if (!currentChatId.value) return;

            isLoadingOlderMessages.value = true;
            try {
                const response = await fetch(route('get-chat-messages', {
                    chat_id: currentChatId.value,
                    page: page.value + 1
                }), {
                    headers: getHeaders()
                });

                if (!response.ok) throw new Error('Failed to load messages');

                const data = await response.json();
                if (!data.messages.data) {
                    hasMoreMessages.value = false;
                    return;
                }

                page.value++;
                prependMessages(data.messages.data);
            } catch (error) {
                console.error('Error loading older messages:', error);
            } finally {
                isLoadingOlderMessages.value = false;
            }
        }

        function prependMessages(messages) {
            const messageContainer = document.querySelector('.messages');
            if (!messageContainer) return;

            const scrollHeightBefore = messageContainer.scrollHeight;
            const scrollTopBefore = messageContainer.scrollTop;
            const sortedMessages = messages.sort((a, b) => {
                return new Date(a.updated_at) - new Date(b.updated_at);
            });
            sortedMessages.forEach((message) => {
                const div = document.createElement('div')
                div.dataset.messageId = message.id;
                if(message.sender_id === props.user_id){
                    div.className = 'sender-message-container'
                }else{
                    div.className = 'receiver-message-container'
                }
                div.innerHTML = `
                    <div class="message-content-wrapper">
                        <p class="message-content">${message.content}</p>
                        <div class="other-message-info-wrapper">
                            <p class='message-data'>${formatDataToUser(message.updated_at)}</p>
                            ${
                    !message.is_read && message.sender_id === props.user_id
                        ? '<div class="blue-circle"></div>'
                        : ''
                }
                        </div>
                    </div>
                `
                messageContainer.prepend(div)
                observer.value.observe(div);
            })
            messageContainer.scrollTop = messageContainer.scrollHeight - scrollHeightBefore + scrollTopBefore;
            setupScrollListener();
        }
        async function showChat(user) {
            if (isChatLoading.value || loading.value) return;
            isInputFocused.value = false
            isChatLoading.value = true;
            try {
                if (partnerData.value) {
                    partnerData.value.innerHTML = `
                        <div class="concrete_user_name_and_last_seen_wrapper">
                            <h2>${user.username}</h2>
                            ${user.last_seen ? `<h6>${user.last_seen}</h6>` : `<h6>22.06.2024</h6>`}
                        </div>
                        ${user.avatar_url
                        ? `<img src="${user.avatar_url}" alt="${user.username}"/>`
                        : `<div class="fake-avatar concrete_fake_avatar">${user.username[0]}</div>`
                    }
                    `;
                }

                if (currentChatId.value !== null && currentChatLastMessage.value === '') {
                    try {
                        await deletePreviousChat();
                    } catch (error) {
                        console.error('Delete chat error:', error);
                    }
                }

                await createOrGetChat(user);
            } catch (error) {
                console.error('Chat error:', error);
            } finally {
                isChatLoading.value = false;
            }
        }

        async function deletePreviousChat() {
            try {
                const response = await fetch(route('destroy-chat', { chat_id: currentChatId.value }), {
                    method: 'DELETE',
                    headers: getHeaders(),
                });

                if (!response.ok) {
                    throw new Error('Failed to delete chat');
                }

                currentChatId.value = null;
            } catch (error) {
                console.error('Delete chat error:', error);
                throw error;
            }
        }

        async function createOrGetChat(user) {
            try {
                const response = await fetch(route('get-or-create-chat'), {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        participants: [user.id],
                        type: 'private',
                    }),
                    credentials: 'include'
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Failed to create chat');
                }

                const chatData = await response.json();
                if(chatData.last_message){
                    renderChatMessages(chatData.messages.data)
                }
                currentChatId.value = chatData.chat_id;
                currentChatLastMessage.value = chatData.last_message;
            } catch (error) {
                console.error('Create chat error:', error);
                alert(error.message || 'Server error, try again later');
                throw error;
            }
        }
        function renderChat(data) {
            const userChats = document.querySelector('.current-user-chats')
            const li = document.createElement('li')
            li.className = 'current-user'
            li.innerHTML = `
                            <div class="user-data">
                                <div class="fake-user-avatar">${data.sender.username[0]}</div>
                                <div class="name_and_last_message">
                                    <h3 class="username">${data.sender.username}</h3>
                                    <h3 class="last-message">${data.last_message}</h3>
                                </div>
                            </div>
                            <div class="user-last-seen">
                                ${formatDataToUser(data.data)}
                            </div>
                            <input type="hidden" class="chat-id" value="${data.chat_id}">
                    `
            userChats.prepend(li)
            li.addEventListener('click',() => {
                showChat(data.sender)
            })
        }

        function getHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest'
            };
        }

        watch(searchQuery, (newVal) => {
            clearTimeout(debounceTimeout);

            if (newVal.trim().length === 0) {
                users.value = [];
                return;
            }

            debounceTimeout = setTimeout(() => {
                loading.value = true;
                fetch(route('search-users', {username: newVal}), {
                    headers: getHeaders()
                })
                    .then(res => res.json())
                    .then(data => {
                        users.value = data;
                    })
                    .catch(() => {
                        users.value = [];
                    })
                    .finally(() => {
                        loading.value = false;
                    });
            }, 300);
        });

        function sendMessage() {
            if (messageContent.value === '' || !currentChatId.value) return;
            const chatId = currentChatId.value
            const message = messageContent.value
            const tempMessageId = 'temp-' + crypto.randomUUID();
            renderMessage(tempMessageId)
            fetch(route('send-message'), {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({
                    chat_id: currentChatId.value,
                    content: messageContent.value,
                    temp_id: tempMessageId,
                }),
                credentials: 'include'
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    messageContent.value = '';
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    alert('Error sending message');
                })
                .finally(() => {
                    messageContent.value = ''
                    const inputs = document.querySelectorAll('input.chat-id');
                    const input = Array.from(inputs).find(i => i.value === chatId);

                    if (!input) {
                        return;
                    }

                    const parent = input.closest('.current-user');
                    if (!parent) return;
                    const lastMessage = parent.querySelector('.last-message');
                    if (lastMessage) {
                        lastMessage.textContent = message;
                    }
                })
        }

        function renderMessage(tempMessageId)
        {
            const messageContainer = document.querySelector('.messages')
            const newMessage = document.createElement('div');
            newMessage.className = 'sender-message-container';
            newMessage.dataset.messageId = tempMessageId;
            newMessage.innerHTML = `
              <div class="message-content-wrapper">
                <p class="message-content">${messageContent.value}</p>
                <div class="other-message-info-wrapper">
                  <p class="message-data">${getCurrentTime()}</p>
                  <div class="blue-circle"></div>
                </div>
              </div>
            `;
            messageContainer.appendChild(newMessage)
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }

        function renderChatMessages(messages){
            const sortedMessages = messages.sort((a, b) => {
                return new Date(a.updated_at) - new Date(b.updated_at);
            });
            const messageContainer = document.querySelector('.messages')
            messageContainer.innerHTML = ``
            sortedMessages.forEach((message) => {
                const div = document.createElement('div')
                div.dataset.messageId = message.id;
                if(message.sender_id === props.user_id){
                    div.className = 'sender-message-container'
                }else{
                    div.className = 'receiver-message-container'
                }
                div.innerHTML = `
                    <div class="message-content-wrapper">
                        <p class="message-content">${message.content}</p>
                        <div class="other-message-info-wrapper">
                            <p class='message-data'>${formatDataToUser(message.updated_at)}</p>
                            ${
                                !message.is_read && message.sender_id === props.user_id
                                    ? '<div class="blue-circle"></div>'
                                    : ''
                            }
                        </div>
                    </div>
                `
                messageContainer.appendChild(div)
                observer.value.observe(div);
            })
            messageContainer.scrollTop = messageContainer.scrollHeight;
            setupScrollListener();
        }

        function getCurrentTime(){
            const now = new Date();
            return now.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})
        }
        function formatDataToUser(data){
            let iso = data.replace(' ', 'T');
            iso = iso.replace(/(\.\d{3})\d+/, '$1');
            if (!iso.endsWith('Z')) {
                iso += 'Z';
            }
            const date = new Date(iso);
            if (isNaN(date)) {
                return 'Invalid Date';
            }
            return date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        return {
            sendMessage,
            searchQuery,
            users,
            loading,
            isInputFocused,
            showChat,
            messageContent,
            currentChatId,
            partnerData,
            formatDataToUser,
            isLoadingOlderMessages
        };
    }
};
</script>

<style src="../../../css/Home/home.css"></style>
