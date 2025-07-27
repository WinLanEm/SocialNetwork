<template>
    <Head :title="title"/>
    <div class="home-container">
        <div class="profile" v-show="profileIsOpen">
            <div class="sidebar-avatar-and-username">
                <div class="avatar-container">
                    <img v-if="user_data.avatar_url" :src="user_data.avatar_url" :alt="user_data.username" class="user-avatar"/>
                    <div v-else class="fake-user-profile-avatar">{{user_data.username[0]}}</div>
                    <label class="avatar-upload">
                        <input type="file" class="update-avatar" accept="image/*">
                        <span class="upload-icon">+</span>
                    </label>
                </div>
                <h3 class="username">{{user_data.username}}</h3>
            </div>
            <form class="update_profile_data">
                <div>
                    <label for="username">username</label>
                    <input :value="user_data.username" name="username"/>
                </div>
                <div>
                    <label for="phone">phone</label>
                    <input name="phone" :value="user_data.phone"/>
                </div>
                <div class="bio-div">
                    <label for="bio">bio</label>
                    <textarea class="bio" name="bio" :value="user_data.bio"/>
                </div>
                <button type="submit">update</button>
            </form>
        </div>
        <div class="chats" :class="{'sidebar-active':isInputFocused}">
            <transition name="fade">
                <div
                    v-if="sideBarIsOpen || profileIsOpen"
                    class="overlay"
                    @click="handleClick"
                ></div>
            </transition>
            <transition name="slide">
                <aside
                    v-show="sideBarIsOpen"
                    class="sidebar"
                    @click.stop
                >
                    <div class="sidebar-avatar-and-username">
                        <img v-if="user_data.avatar_url" :src="user_data.avatar_url" :alt="user_data.username"/>
                        <div v-else class="fake-user-avatar">{{user_data.username[0]}}</div>
                        <h3 class="username">{{user_data.username}}</h3>
                    </div>
                    <div class="sidebar-active">
                        <button @click="profileIsOpen = true; sideBarIsOpen = false">My profile</button>
                        <button>Settings</button>
                    </div>
                </aside>
            </transition>


            <div class="other_and_search">
                <div class="other-btn">
                    <button class="profile-button" @click="sideBarIsOpen = !sideBarIsOpen">
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
                        <li v-for="chat in internalChats" :key="chat.id" class="current-user" @mousedown.prevent @click="showChat(chat.recipient.data)">
                            <div class="user-data">
                                <div class="fake-user-avatar">{{chat.recipient.data.username[0]}}</div>
                                <div class="name_and_last_message">
                                    <div class="username_and_circle">
                                        <h3 class="username">{{ chat.recipient.data.username }}</h3>
                                        <div
                                            class="blue-circle blue-circle-in-channel"
                                            :style="{ display: chat.chat_data.is_read && chat.recipient.id !== user_id ? 'none' : 'block' }"
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
                <div class="chat-partner-data" ref="partnerData">
                    <div class="concrete_user_name_and_last_seen_wrapper">
                        <h2>{{partnerInfo.username}}</h2>
                        <h6 v-if="partnerInfo.lastSeen">{{partnerInfo.lastSeen}}</h6>
                        <h6 v-else>latest ago</h6>
                    </div>
                    <img v-if="partnerInfo.avatar" :alt="partnerInfo.username" :src="partnerInfo.avatar"/>
                    <div v-else class="fake-avatar concrete_fake_avatar">{{partnerInfo.username[0]}}</div>
                </div>
                <div class="chat-partner-buttons"></div>
            </div>
            <div class="messages-container" :class="{'inactive-message-container': !currentChatId}">
                <h3 v-if="!currentChatId">Select a chat to start messaging</h3>
                <ul class="messages">
                    <li v-if="isLoadingOlderMessages" class="loading-indicator">
                        Loading older messages...
                    </li>
                    <li v-for="message in messages" :key="message.id" :data-message-id="message.id" :class="message.sender_id === user_id ? 'sender-message-container' : 'receiver-message-container'" :id="message.is_read ? 'readed' : 'unreaded'">
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
import {ref, watch, onMounted, onUnmounted, nextTick} from "vue";

export default {
    components: {
        Head
    },
    props: {
        title: String,
        user_id: Number,
        chats: Array,
        user_data:Array,
    },
    methods:{
        handleClick() {
            this.closeSidebar();
            this.profileIsOpen = false;
        },
        toggleSidebar() {
            this.sideBarIsOpen = !this.sideBarIsOpen;
        },
        closeSidebar() {
            this.sideBarIsOpen = false;
        },
        closeProfile() {
            this.profileIsOpen = false;
        },
        handleClickOutside(e) {
            if (this.sideBarIsOpen && !this.$refs.sidebar.contains(e.target)) {
                this.closeSidebar();
            }
        }
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    setup(props) {
        const profileIsOpen = ref(false)
        const sideBarIsOpen = ref(false)
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
        const messages = ref([]);
        const partnerInfo = ref({
            username: '',
            lastSeen: '',
            avatar: ''
        })
        const internalChats = ref([...props.chats]);
        let debounceTimer;
        const visibleIdsBatch = ref(new Set());
        watch(currentChatId, (newChatId, oldChatId) => {
            if(newChatId) {
                nextTick(() => {
                    setupMessageVisibilityObserver();
                });
                // Отписываемся от старого канала
                if(oldChatId) {
                    window.Echo.leave(`message_in_chat.${oldChatId}`);
                }

                window.Echo.private(`message_in_chat.${newChatId}`)
                    .listen('.message.created', data => {
                        if(data.content && data.sender_id !== props.user_id){
                            data.is_read = true
                            messages.value.push(data)
                            readWebsocketMessage(data.id,newChatId,props.user_id)
                        }
                    });
            }
        }, {immediate: true});

        onMounted(() => {
            csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            window.Echo.private(`chat_render_by_user.${Number(props.user_id)}`)
                .listen('.chat.render', data => {
                    internalChats.value = [{
                        id: data.chat_id,
                        recipient: {
                            data: {
                                username: data.sender.username,
                                avatar_url: data.sender.avatar_url,
                                id: data.sender.id
                            }
                        },
                        chat_data: {
                            chat_id: data.chat_id,
                            last_message: data.last_message,
                            is_read: data.is_read && data.sender.id !== props.user_id,
                            updated_at: data.data
                        }
                    }, ...internalChats.value];
                });
            window.Echo.private(`last_message.${props.user_id}`)
                .listen('.last_message.updated',updateLastMessage);
            window.Echo.private(`fail_store_message_user.${props.user_id}`)
                .listen('.message.failed',data => {
                    alert(data.error)
                })
            window.Echo.private(`read_messages_user.${props.user_id}`)
                .listen('.messages.read',renderMessagesAsRead)
            window.Echo.private(`make_chat_is_read_user.${props.user_id}`)
                .listen('.chat.is_read',markChatAsRead)
            window.Echo.private(`send_real_id.${props.user_id}`)
                .listen('.message_id.updated',updateMessageId)

            setupMessageVisibilityObserver();
        });
        onUnmounted(() => {
            if (observer.value) {
                observer.value.disconnect();
            }
        })
        const updateMessageId = (data) => {
            const index = messages.value.findIndex(m => m.id === data.temp_id);
            if (index !== -1) {
                const newMessage = {
                    ...messages.value[index],
                    id: data.message_id
                };
                messages.value.splice(index, 1, newMessage);
            }
        };
        const markChatAsRead = (data) => {
            internalChats.value = internalChats.value.map(chat => {
                if (chat.chat_data.chat_id === data.chat_id) {
                    return {
                        ...chat,
                        chat_data: {
                            ...chat.chat_data,
                            is_read: true
                        }
                    };
                }
                return chat;
            });
        };
        const renderMessagesAsRead = (data) => {
            if (currentChatId.value !== data.chat_id) return;

            messages.value = messages.value.map(message => {
                if (data.message_ids.includes(message.id)) {
                    return {
                        ...message,
                        is_read: true
                    };
                }
                return message;
            });
        };
        const updateLastMessage = (data) => {
            const chatIndex = internalChats.value.findIndex(
                chat => chat.chat_data.chat_id === data.chat_id
            );
            if (chatIndex === -1) {
                console.warn('Чат не найден:', data.chat_id);
                return;
            }

            internalChats.value[chatIndex].chat_data.last_message = data.content;
            internalChats.value[chatIndex].chat_data.updated_at = data.updated_at;

            if (currentChatId.value !== data.chat_id) {
                internalChats.value[chatIndex].chat_data.is_read = false;
            }

            internalChats.value = [...internalChats.value];
        };
        const setupMessageVisibilityObserver = () => {
            if (observer.value) {
                observer.value.disconnect();
            }

            const messagesContainer = document.querySelector('.messages');
            if (!messagesContainer) return;

            observer.value = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const messageId = entry.target.dataset.messageId;
                    if (!messageId) return;

                    const isReceiverMessage = entry.target.classList.contains('receiver-message-container');
                    const isUnread = entry.target.id === 'unreaded';

                    if (entry.isIntersecting && isReceiverMessage && isUnread) {
                        visibleMessageIds.value.add(messageId);
                        visibleIdsBatch.value.add(messageId);
                    } else {
                        visibleMessageIds.value.delete(messageId);
                        visibleIdsBatch.value.delete(messageId);
                    }
                });

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (visibleIdsBatch.value.size > 0) {
                        markMessagesAsRead();
                        visibleIdsBatch.value.clear();
                    }
                }, 1000);
            }, {
                root: messagesContainer,
                rootMargin: '100px',
                threshold: 0.1
            });

            document.querySelectorAll('.receiver-message-container').forEach(el => {
                observer.value.observe(el);
            });
        };

        const markMessagesAsRead = async () => {
            if (visibleMessageIds.value.size === 0 || !currentChatId.value) return;

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
                messages.value = messages.value.map(msg => {
                    if (visibleMessageIds.value.has(msg.id)) {
                        return { ...msg, is_read: true };
                    }
                    return msg;
                });
                visibleMessageIds.value.clear();
            } catch (error) {
                console.error('Error marking messages as read:', error);
            }
        };
        function setupScrollListener() {
            const messagesContainer = document.querySelector('.messages');
            if (!messagesContainer) return;
            messagesContainer.addEventListener('scroll', async () => {
                if (messagesContainer.scrollTop < 1000 &&
                    !isLoadingOlderMessages.value &&
                    hasMoreMessages.value) {
                    await loadOlderMessages();
                }
            });
        }
        async function loadOlderMessages() {
            if (!currentChatId.value && !hasMoreMessages.value) return;

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
                if (data.messages.data.length === 0) {
                    hasMoreMessages.value = false;
                    return;
                }
                page.value++;
                messages.value = [...data.messages.data, ...messages.value];
                setupScrollListener();
            } catch (error) {
                console.error('Error loading older messages:', error);
            } finally {
                isLoadingOlderMessages.value = false;
            }
        }

        async function showChat(user) {
            if (isChatLoading.value || loading.value) return;
            isInputFocused.value = false
            isChatLoading.value = true;
            try {
                partnerInfo.value = {
                    username: user.username,
                    lastSeen: user.last_seen ? user.last_seen : 'ago',
                    avatar: user.avatar_url || null
                }
                if (currentChatId.value !== null && currentChatLastMessage.value === '') {
                    try {
                        await deletePreviousChat();
                    } catch (error) {
                        console.error('Delete chat error:', error);
                    }
                }
                if(currentChatId.value){
                    leaveChat(currentChatId.value)
                }
                await createOrGetChat(user);
                nextTick(() => {
                    const messagesContainer = document.querySelector('.messages');
                    if (messagesContainer) {
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;

                        setupMessageVisibilityObserver();
                    }
                });
            } catch (error) {
                console.error('Chat error:', error);
            } finally {
                isChatLoading.value = false;
            }
        }
        async function leaveChat(chatId) {
            try {
                window.Echo.leave(`message_in_chat.${chatId}`);
            } catch (error) {
                console.error('Leave chat error:', error);
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
                    throw new Error(errorData.message || 'Failed to create chat');
                }

                const chatData = await response.json();
                if(chatData.last_message){
                    messages.value = [...chatData.messages.data].sort((a, b) => {
                        return new Date(a.updated_at) - new Date(b.updated_at);
                    });
                    nextTick(() => {
                        setupScrollListener()
                    })
                }else{
                    messages.value = []
                }
                currentChatId.value = chatData.chat_id;
                currentChatLastMessage.value = chatData.last_message;
            } catch (error) {
                console.error('Create chat error:', error);
                alert(error.message || 'Server error, try again later');
                throw error;
            }
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

        async function sendMessage() {
            if (messageContent.value === '' || !currentChatId.value) return;
            const chatId = currentChatId.value
            const messageText = messageContent.value
            const tempMessageId = 'temp-' + crypto.randomUUID();
            messages.value.push({
                id: tempMessageId,
                content: messageText,
                sender_id: props.user_id,
                is_read: false,
                updated_at: getCurrentTime(),
            });
            const data = {
                chat_id: chatId,
                content: messageText,
                updated_at: getCurrentTime()
            }
            updateLastMessage(data)
            messageContent.value = ''

            await nextTick();

            const messagesContainer = document.querySelector('.messages');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
            try {
                const response = await fetch(route('send-message'), {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({
                        chat_id: chatId,
                        content: messageText,
                        temp_id: tempMessageId,
                    }),
                    credentials: 'include'
                });

                if (!response.ok) throw new Error('Failed to send message');
            } catch (error) {
                console.error('Error sending message:', error);
                messages.value = messages.value.filter(m => m.id !== tempMessageId);
            }
        }

        function getCurrentTime(){
            const now = new Date();
            return now.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})
        }
        function formatDataToUser(data) {
            // Если уже отформатированное время (HH:MM)
            if (typeof data === 'string' && /^\d{1,2}:\d{2}$/.test(data)) {
                return data;
            }

            let date;

            if (typeof data === 'number') {
                // timestamp в секундах или миллисекундах
                date = new Date(data > 9999999999 ? data : data * 1000);
            } else if (typeof data === 'string') {
                // пытаемся создать дату напрямую
                date = new Date(data);

                if (isNaN(date)) {
                    // Удаляем лишние микросекунды — максимально 3 знака после точки
                    // Например: 2025-07-26T11:24:07.932000Z -> 2025-07-26T11:24:07.932Z
                    let cleaned = data.replace(/\.(\d{3})\d+/, '.$1');

                    // Убедимся, что заканчивается на 'Z' (UTC)
                    if (!cleaned.endsWith('Z')) {
                        cleaned += 'Z';
                    }

                    date = new Date(cleaned);

                    if (isNaN(date)) {
                        // Финальный запасной вариант — просто убрать дробную секунду полностью
                        cleaned = data.replace(/\.\d+/, '');
                        date = new Date(cleaned);
                    }
                }
            } else {
                return 'Некорректный формат даты';
            }

            if (isNaN(date)) {
                console.error('Не удалось распарсить дату:', data);
                return 'Некорректная дата';
            }

            // Форматируем в 24-часовом формате, с ведущими нулями в часах и минутах
            return date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }

        async function readWebsocketMessage(messageId,chatId,userId) {
            await fetch(route('mark-messages-as-read'), {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({
                    message_ids: [messageId],
                    chat_id: chatId,
                    user_id: userId
                })
            })
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
            isLoadingOlderMessages,
            partnerInfo,
            messages,
            internalChats,
            sideBarIsOpen,
            profileIsOpen,
        };
    }
};
</script>

<style src="../../../css/Home/home.css"></style>

