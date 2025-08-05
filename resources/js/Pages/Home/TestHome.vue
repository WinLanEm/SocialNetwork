<template>
    <Head :title="title"/>
    <div class="home-container">
        <ProfileSidebar
            :isOpen="profileIsOpen"
            :initialUserData="userData"
            @profile-updated="handleProfileUpdate"
        />

        <div class="chats">
            <transition name="fade">
                <div
                    v-if="sideBarIsOpen || profileIsOpen"
                    class="overlay"
                    @click="handleClick"
                ></div>
            </transition>

            <MainSidebar
                :isOpen="sideBarIsOpen"
                :userData="userData"
                @open-profile="openProfile"
            />

            <SearchBar
                @toggle-sidebar="toggleSidebar"
                @input-focus="isInputFocused = $event"
                @results="users = $event"
                @loading="loading = $event"
            />

            <ChatList
                :isInputFocused="isInputFocused"
                :users="users"
                :internalChats="internalChats"
                :user_id="user_id"
                :formatDataToUser="formatDataToUser"
                @show-chat="showChat"
            />
        </div>

        <div class="concrete-chat-wrapper">
            <input type="hidden" class="current_chat_id" v-model="currentChatId">

            <ChatHeader
                :currentChatId="currentChatId"
                :partnerInfo="partnerInfo"
                :formatDataToUser="formatDataToUser"
            />

            <ChatMessages
                :currentChatId="currentChatId"
                :messages="messages"
                :user_id="user_id"
                :isLoadingOlderMessages="isLoadingOlderMessages"
                :formatDataToUser="formatDataToUser"
            />

            <MessageInput
                v-model="messageContent"
                :currentChatId="currentChatId"
                @send-message="sendMessage"
            />
        </div>
    </div>
</template>

<script>
import {Head} from "@inertiajs/vue3";
import {ref, watch, onMounted, onUnmounted, nextTick} from "vue";
import { debounce } from 'lodash-es';
import ProfileSidebar from './Components/ProfileSidebar.vue';
import MainSidebar from './Components/MainSidebar.vue';
import ChatList from './Components/ChatList.vue';
import ChatHeader from './Components/ChatHeader.vue';
import ChatMessages from './Components/ChatMessages.vue';
import MessageInput from './Components/MessageInput.vue';
import SearchBar from './Components/SearchBar.vue';

export default {
    components: {
        Head,
        ProfileSidebar,
        MainSidebar,
        ChatList,
        ChatHeader,
        ChatMessages,
        MessageInput,
        SearchBar
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
        const users = ref([]);
        const loading = ref(false);
        const isInputFocused = ref(false);
        const isChatLoading = ref(false);
        const partnerData = ref(null);
        const visibleMessageIds = ref(new Set());
        const observer = ref(null);
        const csrfToken = ref('');
        const isLoadingOlderMessages = ref(false);
        const hasMoreMessages = ref(true);
        const page = ref(1);
        const messages = ref([]);
        let lastSeenInterval = null;
        const partnerInfo = ref({
            username: '',
            lastSeen: '',
            avatar: '',
            id: ''
        })
        console.log(props.chats)
        watch(() => partnerInfo.value.id, (newPartnerId, oldPartnerId) => {
                if(newPartnerId){
                    nextTick(() => {
                        if(oldPartnerId) {
                            window.Echo.leave(`last_seen.${oldPartnerId}`);
                        }

                        window.Echo.private(`last_seen.${newPartnerId}`)
                            .listen('.user.offline', data => {
                                partnerInfo.value.lastSeen = formatDataToUser(data.last_seen)
                            })
                            .listen('.user.online', data => {
                                partnerInfo.value.lastSeen = data.last_seen
                        });

                    })
                }
            }, { deep: true })
        ;
        const userData = ref({
            username: props.user_data.username,
            avatar_url: props.user_data.avatar_url,
            bio:props.user_data.bio,
            phone:props.user_data.phone
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
            updateLastSeen();
            lastSeenInterval = setInterval(updateLastSeen, 45000);
            window.Echo.private(`chat_render_by_user.${Number(props.user_id)}`)
                .listen('.chat.render', data => {
                    console.log(data)
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
                            is_read: data.is_read,
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
            if (lastSeenInterval) {
                clearInterval(lastSeenInterval);
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
            await updateLastSeen();
            isInputFocused.value = false
            isChatLoading.value = true;
            try {
                partnerInfo.value = {
                    username: user.username,
                    lastSeen: user.last_seen || null,
                    avatar: user.avatar_url || null,
                    id: user.id,
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
                partnerInfo.value.lastSeen = chatData.last_seen
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
        function formatData(data) {
            return data.toLocaleTimeString([], {
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
        const updateLastSeen = debounce(async () => {
            try {
                await fetch(route('update-last-seen'), {
                    method: 'POST',
                    body: JSON.stringify({
                        last_seen: getCurrentLastSeen() // Отправляем текущее время
                    }),
                    headers: getHeaders(),
                });
            } catch (error) {
                console.error('Error updating last seen:', error);
            }
        }, 30000);
        function getCurrentLastSeen() {
            const now = new Date();

            // Получаем локальные компоненты даты
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            // Формат для БД: YYYY-MM-DD HH:MM:SS
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }
        const handleProfileUpdate = (updatedData) => {
            userData.value = {...userData.value, ...updatedData}
        }
        const openProfile = () => {
            sideBarIsOpen.value = false;
            profileIsOpen.value = true;
        };
        return {
            openProfile,
            handleProfileUpdate,
            userData,
            sendMessage,
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
            getCurrentTime,
            formatData
        };
    }
};
</script>

<style src="../../../css/Home/home.css"></style>

