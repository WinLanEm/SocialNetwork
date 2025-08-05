<template>
    <div class="chat-partner-wrapper" :class="{'inactive-chat-partner-wrapper': !currentChatId}">
        <div class="chat-partner-data" ref="partnerData">
            <div class="concrete_user_name_and_last_seen_wrapper">
                <h2>{{partnerInfo.username}}</h2>
                <h6 v-if="partnerInfo.lastSeen">{{formatDataToUser(partnerInfo.lastSeen)}}</h6>
                <h6 v-else>latest ago</h6>
            </div>
            <img class="fake-avatar concrete_fake_avatar" v-if="partnerInfo.avatar" :alt="partnerInfo.username" :src="partnerInfo.avatar"/>
            <div v-else class="fake-avatar concrete_fake_avatar">{{partnerInfo.username[0]}}</div>
        </div>
        <div class="chat-partner-buttons"></div>
    </div>
</template>

<script>
export default {
    props: {
        currentChatId: [Number, String],
        partnerInfo: Object,
        formatDataToUser: Function
    }
};
</script>
