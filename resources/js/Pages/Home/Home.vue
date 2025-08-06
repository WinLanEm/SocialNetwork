<template>
    <Head :title="title"/>
    <div class="home-container">
        <!-- Сайдбар профиля (умный компонент) -->
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

            <!-- Главный сайдбар (глупый компонент) -->
            <MainSidebar
                :isOpen="sideBarIsOpen"
                :userData="userData"
                @open-profile="openProfile"
            />

            <!-- Поиск (умный компонент) -->
            <SearchBar
                @toggle-sidebar="toggleSidebar"
                @input-focus="isInputFocused = $event"
                @results="users = $event"
                @loading="loading = $event"
            />

            <!-- Список чатов (компонент-контейнер) -->
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
            <!-- Хедер чата (умный компонент) -->
            <ChatHeader
                :currentChatId="currentChatId"
                :initialPartnerData="partnerInfo"
                :formatDataToUser="formatDataToUser"
            />

            <!-- Сообщения чата (умный компонент) -->
            <ChatMessages
                :currentChatId="currentChatId"
                :user_id="user_id"
                :formatDataToUser="formatDataToUser"
                :newMessage="newMessageForChat"
            />

            <!-- Поле ввода сообщения (умный компонент) -->
            <MessageInput
                :currentChatId="currentChatId"
                :user_id="user_id"
                @message-sent="handleMessageSent"
            />
        </div>
    </div>
</template>

<script>
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
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
        Head, ProfileSidebar, MainSidebar, ChatList, ChatHeader, ChatMessages, MessageInput, SearchBar
    },
    props: {
        title: String,
        user_id: Number,
        chats: Array,
        user_data: Array,
    },
    setup(props) {
        // --- СОСТОЯНИЕ UI ---
        const profileIsOpen = ref(false);
        const sideBarIsOpen = ref(false);
        const isInputFocused = ref(false);
        const loading = ref(false); // Управляется из SearchBar
        const isChatLoading = ref(false);

        // --- СОСТОЯНИЕ ДАННЫХ ---
        const userData = ref({ ...props.user_data });
        const users = ref([]); // Найденные пользователи, управляются из SearchBar
        const internalChats = ref([...props.chats]);
        const currentChatId = ref(null);
        const partnerInfo = ref({});
        const newMessageForChat = ref(null); // Мост между MessageInput и ChatMessages

        const csrfToken = ref('');
        let lastSeenInterval = null;

        // --- УТИЛИТЫ ---
        const getHeaders = () => ({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken.value
        });

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

        // --- ОСНОВНАЯ ЛОГИКА КООРДИНАТОРА ---

        // Открытие/закрытие сайдбаров
        const openProfile = () => { sideBarIsOpen.value = false; profileIsOpen.value = true; };
        const toggleSidebar = () => { sideBarIsOpen.value = !sideBarIsOpen.value; };
        const handleClick = () => { sideBarIsOpen.value = false; profileIsOpen.value = false; };

        // Обработчики событий от дочерних компонентов
        const handleProfileUpdate = (updatedData) => { userData.value = { ...userData.value, ...updatedData }; };
        const handleMessageSent = (message) => {
            newMessageForChat.value = message;
            updateLastMessage(message);
        };

        // Логика, которая управляет состоянием, живущим в Home.vue (списком чатов)
        const updateLastMessage = (data) => {
            const chatIndex = internalChats.value.findIndex(chat => chat.chat_data.chat_id === data.chat_id);
            if (chatIndex !== -1) {
                internalChats.value[chatIndex].chat_data.last_message = data.content;
                internalChats.value[chatIndex].chat_data.updated_at = data.updated_at;
                if (currentChatId.value !== data.chat_id) {
                    internalChats.value[chatIndex].chat_data.is_read = false;
                }
            }
        };

        const markChatAsRead = (data) => {
            const chat = internalChats.value.find(c => c.chat_data.chat_id === data.chat_id);
            if (chat) chat.chat_data.is_read = true;
        };

        // Главная функция-оркестратор при выборе чата
        const showChat = async (user) => {
            if (isChatLoading.value) return;
            isInputFocused.value = false; // Скрываем результаты поиска

            // 1. Получаем или создаем чат
            isChatLoading.value = true;
            try {
                const response = await fetch(route('get-or-create-chat'), {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({ participants: [user.id], type: 'private' })
                });
                const chatData = await response.json();

                // 2. Устанавливаем ID текущего чата и информацию о партнере
                // Это вызовет ре-рендер и запуск логики в ChatHeader и ChatMessages
                currentChatId.value = chatData.chat_id;
                partnerInfo.value = {
                    id: user.id,
                    username: user.username,
                    avatar: user.avatar_url,
                    lastSeen: chatData.last_seen
                };

            } catch (error) {
                console.error('Chat error:', error);
            } finally {
                isChatLoading.value = false;
            }
        };

        const updateLastSeen = debounce(async () => {
            try {
                await fetch(route('update-last-seen'), {
                    method: 'POST',
                    headers: getHeaders(), // Эта функция у вас уже есть
                    body: JSON.stringify({
                        last_seen: getCurrentLastSeen()
                    }),
                });
            } catch (error) {
                console.error('Error updating last seen:', error);
            }
        }, 30000);

        // --- ГЛОБАЛЬНЫЕ СЛУШАТЕЛИ WEBSOCKETS ---
        onMounted(() => {
            csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            updateLastSeen();
            lastSeenInterval = setInterval(updateLastSeen, 45000);

            // Слушатели, влияющие на состояние, которым управляет Home (список чатов)
            window.Echo.private(`chat_render_by_user.${props.user_id}`)
                .listen('.chat.render', (data) => { internalChats.value.unshift(data.chat); });

            window.Echo.private(`last_message.${props.user_id}`)
                .listen('.last_message.updated', updateLastMessage);

            window.Echo.private(`make_chat_is_read_user.${props.user_id}`)
                .listen('.chat.is_read', markChatAsRead);

            window.Echo.private(`fail_store_message_user.${props.user_id}`)
                .listen('.message.failed', (data) => { alert(data.error); });
        });

        onUnmounted(() => {
            if (lastSeenInterval) clearInterval(lastSeenInterval);
            // Отписываться от каналов Echo не обязательно, он делает это сам при уходе со страницы
        });

        function getCurrentLastSeen() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }


        return {
            profileIsOpen, sideBarIsOpen, isInputFocused, loading, userData, users, internalChats,
            currentChatId, partnerInfo, newMessageForChat,
            handleClick, toggleSidebar, openProfile, handleProfileUpdate, handleMessageSent, showChat,
            updateLastMessage, formatDataToUser
        };
    }
};
</script>

<style src="../../../css/Home/home.css"></style>
