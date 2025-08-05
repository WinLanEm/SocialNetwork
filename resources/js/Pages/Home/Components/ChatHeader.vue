<template>
    <!-- Шаблон остается почти без изменений -->
    <div class="chat-partner-wrapper" :class="{'inactive-chat-partner-wrapper': !currentChatId}">
        <!-- Добавляем проверку, что partnerInfo существует -->
        <div v-if="partnerInfo.id" class="chat-partner-data" ref="partnerData">
            <div class="concrete_user_name_and_last_seen_wrapper">
                <h2>{{ partnerInfo.username }}</h2>
                <h6 v-if="partnerInfo.lastSeen">{{ formatDataToUser(partnerInfo.lastSeen) }}</h6>
                <h6 v-else>offline</h6>
            </div>
            <img class="fake-avatar concrete_fake_avatar" v-if="partnerInfo.avatar" :alt="partnerInfo.username" :src="partnerInfo.avatar"/>
            <div v-else class="fake-avatar concrete_fake_avatar">{{ partnerInfo.username ? partnerInfo.username[0] : '' }}</div>
        </div>
        <div class="chat-partner-buttons"></div>
    </div>
</template>

<script>
import { ref, watch, onUnmounted, nextTick } from 'vue';

export default {
    props: {
        currentChatId: [Number, String, null],
        // Prop переименован для ясности
        initialPartnerData: {
            type: Object,
            required: true
        },
        formatDataToUser: {
            type: Function,
            required: true
        }
    },
    setup(props) {
        // Локальное реактивное состояние для информации о собеседнике
        const partnerInfo = ref({});

        // Основная логика подписки на события Echo
        const subscribeToPartnerStatus = (newPartnerId, oldPartnerId) => {
            if (oldPartnerId) {
                window.Echo.leave(`last_seen.${oldPartnerId}`);
            }

            if (newPartnerId) {
                window.Echo.private(`last_seen.${newPartnerId}`)
                    .listen('.user.offline', data => {
                        // Обновляем только lastSeen, чтобы не перерисовывать все
                        if (partnerInfo.value.id === data.user_id) {
                            partnerInfo.value.lastSeen = props.formatDataToUser(data.last_seen);
                        }
                    })
                    .listen('.user.online', data => {
                        if (partnerInfo.value.id === data.user_id) {
                            partnerInfo.value.lastSeen = data.last_seen; // 'online'
                        }
                    });
            }
        };

        // Следим за изменением initialPartnerData, которое передает родитель
        watch(() => props.initialPartnerData, (newPartner) => {
            const oldPartnerId = partnerInfo.value.id;
            partnerInfo.value = {
                id: newPartner.id,
                username: newPartner.username,
                avatar: newPartner.avatar,
                lastSeen: newPartner.lastSeen
            };

            // Запускаем подписку/отписку, когда собеседник меняется
            nextTick(() => {
                if (newPartner.id !== oldPartnerId) {
                    subscribeToPartnerStatus(newPartner.id, oldPartnerId);
                }
            });

        }, { deep: true, immediate: true });

        // Отписываемся от канала при размонтировании компонента
        onUnmounted(() => {
            if (partnerInfo.value.id) {
                window.Echo.leave(`last_seen.${partnerInfo.value.id}`);
            }
        });

        return {
            partnerInfo
        };
    }
};
</script>
