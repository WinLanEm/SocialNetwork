<template>
    <div class="message-input-wrapper" :class="{'inactive-message-input': !currentChatId}">
        <input type="text"
               v-model="messageContent"
               @keydown.enter="sendMessage"
               placeholder="Write a message..."
               class="message-write-input"
        >
        <button class="message-input-btn" @click="sendMessage"></button>
        <span
            class="message-input-btn"
            @click="sendMessage"
            v-if="messageContent.length > 0"
        >
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 4L3 11L9 13L15 21L17 15L21 4Z" fill="#2A5885" stroke="#2A5885" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M3 11L21 4L9 13" stroke="white" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M15 21L17 15L9 13" stroke="#2A5885" stroke-width="1.5" stroke-linejoin="round"/>
                    </svg>
                </span>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    props: {
        currentChatId: [Number, String, null],
        user_id: Number
    },
    emits: ['message-sent'],
    setup(props, { emit }) {
        const messageContent = ref('');
        const csrfToken = ref(document.querySelector('meta[name="csrf-token"]')?.content || '');

        const sendMessage = async () => {
            const content = messageContent.value.trim();
            if (content === '' || !props.currentChatId) return;

            const tempId = 'temp-' + crypto.randomUUID();

            // 1. Сообщаем родителю, чтобы он передал это в ChatMessages для оптимистичного обновления
            emit('message-sent', {
                id: tempId,
                content: content,
                sender_id: props.user_id,
                is_read: false,
                updated_at: new Date().toISOString(),
                chat_id: props.currentChatId
            });

            // 2. Очищаем поле ввода
            messageContent.value = '';

            // 3. Отправляем запрос на сервер
            try {
                const response = await fetch(route('send-message'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.value,
                    },
                    body: JSON.stringify({
                        chat_id: props.currentChatId,
                        content: content,
                        temp_id: tempId,
                    })
                });
                if (!response.ok) throw new Error('Failed to send message');
            } catch (error) {
                console.error('Error sending message:', error);
                // Здесь можно добавить логику для отображения ошибки отправки
            }
        };

        return {
            messageContent,
            sendMessage
        };
    }
}
</script>
