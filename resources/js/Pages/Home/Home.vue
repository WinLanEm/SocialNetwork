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
                <div>
                    <div class="users_not_found" v-if="users.length === 0">No users found</div>
                    <ul class="members-searched">
                        <li v-for="user in users" :key="user.id" @click="showChat(user)">
                            <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.username"/>
                            <div class="fake-avatar" v-else>{{user.username[0]}}</div>
                            <div class="name_and_last_seen">
                                <h3>{{user.username}}</h3>
                                <h6 v-if="user.last_seen">{{user.last_seen}}</h6>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="concrete-chat-wrapper">
            <input type="hidden" class="current_chat_id" v-model="currentChatId">
                <div class="chat-partner-wrapper inactive-chat-partner-wrapper">
                    <div class="chat-partner-data">

                    </div>
                    <div class="chat-partner-buttons">

                    </div>
                </div>
                <div class="messages-container inactive-message-container">
                    <h3>Select a chat to start messaging</h3>
                </div>
                <div class="message-wrapper inactive-message-wrapper">
                    <input class="message-write-input"
                           type="text"
                           name="message"
                           placeholder="Write a message..."
                           v-model="messageContent"
                           @keyup.enter="sendMessage"
                    />
                    <span
                        class="message-input-btn"
                        v-if="messageContent.length > 0"
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
import {nextTick, ref, watch} from "vue";
export default {
    components:{
        Head
    },
    props:{
        title:String
    },
    setup(){
        const currentChatLastMessage = ref('');
        const currentChatId = ref(null);
        const consumerId = ref(null);
        const messageContent = ref('');
        const searchQuery = ref('');
        const users = ref([])
        const loading = ref(false)
        const isInputFocused = ref(false)
        let debounceTimeout = null
        let isChatLoading = false
        async function showChat(user){
            if (isChatLoading) return
            isChatLoading = true
            const partnerWrapper = document.querySelector('.chat-partner-data')
            const partnerChatWrapper = document.querySelector('.chat-partner-wrapper')
            const messageContainer = document.querySelector('.messages-container')
            const messageWrapper = document.querySelector('.message-wrapper')
            partnerChatWrapper.classList.remove('inactive-chat-partner-wrapper')
            messageContainer.classList.remove('inactive-message-container')
            messageWrapper.classList.remove('inactive-message-wrapper')
            partnerWrapper.innerHTML = `
                <div class="concrete_user_name_and_last_seen_wrapper">
                    <h2>${user.username}</h2>
                    ${user.last_seen
                ?  `<h6>${user.last_seen}</h6>`
                : `<h6>22.06.2024</h6>`
            }
                </div>
                ${user.avatar_url
                ? `<img src="${user.avatar_url}" alt="${user.username}"/>`
                : `<div class="fake-avatar concrete_fake_avatar">${user.username[0]}</div>`
            }
            `
            messageContainer.textContent = `

            `
            consumerId.value = user.id
            if(currentChatId.value !== null && currentChatLastMessage.value === ''){
                try {
                    await deletePreviousChat();
                } catch (error) {
                    console.error('Delete chat error:', error);
                }
            }
            await createNewChat(user);
            isChatLoading = false;
        }
        async function deletePreviousChat(user){
            const response = await fetch(route('destroy-chat', { chat_id: currentChatId.value }), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
            });

            if (!response.ok) {
                throw new Error('Failed to delete chat');
            }

            console.log(`Chat with id ${currentChatId.value} was deleted`);
            currentChatId.value = null;
        }
        async function createNewChat(user){
            const response = await fetch(route('get-or-create-chat'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    participants: [user.id],
                    type: 'private',
                }),
                credentials: 'include'
            });

            if (!response.ok) {
                alert('Server error, try again later');
                throw new Error('Failed to create chat');
            }

            const chatData = await response.json();
            currentChatId.value = chatData.chat_id;
            currentChatLastMessage.value = chatData.last_message;
            console.log(currentChatId.value);
        }
        watch(searchQuery,(newVal) => {
            clearTimeout(debounceTimeout)
            if(newVal.trim().length === 0){
                users.value = [];
                return
            }
            debounceTimeout = setTimeout(() => {
                loading.value = true
                fetch(route('search-users',{username:newVal}))
                    .then(res => res.json())
                    .then(data => {
                        users.value = data
                    })
                    .catch(() => {
                        users.value = []
                    })
                    .finally(() => {
                        loading.value = false
                    })
            },200)
        })
        function sendMessage(){
            if(messageContent.value === '') return
            fetch(route('send-message'),{
                method:'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    chat_id:currentChatId.value,
                    content: messageContent.value
                }),
                credentials: 'include'
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    messageContent.value = '';
                })
                .catch(error => {
                    console.log(error)
                    alert('Error sending message')
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
        }
    }
}
</script>

<style src="../../../css/Home/home.css"></style>
