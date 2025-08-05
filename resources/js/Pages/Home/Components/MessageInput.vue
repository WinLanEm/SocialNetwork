<template>
    <div class="message-wrapper" :class="{'inactive-message-wrapper': !currentChatId}">
        <input class="message-write-input"
               type="text"
               name="message"
               placeholder="Write a message..."
               v-model="messageContent"
               @keyup.enter="$emit('send-message')"
               :disabled="!currentChatId"
        />
        <span
            class="message-input-btn"
            v-if="messageContent.length > 0 && currentChatId"
            @click="$emit('send-message')"
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
export default {
    props: {
        currentChatId: [Number, String],
        modelValue: String
    },
    emits: ['update:modelValue', 'send-message'],
    computed: {
        messageContent: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    }
};
</script>
