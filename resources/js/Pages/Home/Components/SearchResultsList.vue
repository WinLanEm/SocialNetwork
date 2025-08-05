<template>
    <div>
        <div class="users_not_found" v-if="!users || users.length === 0">No users found</div>
        <ul v-else class="members-searched">
            <li v-for="user in users" :key="user.id" @mousedown.prevent @click="$emit('show-chat', user)">
                <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.username"/>
                <div class="fake-avatar" v-else>{{ user.username[0] }}</div>
                <div class="name_and_last_seen">
                    <h3>{{ user.username }}</h3>
                    <h6 v-if="user.last_seen">{{ formatDataToUser(user.last_seen) }}</h6>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        users: Array,
        formatDataToUser: {
            type: Function,
            required: true
        }
    },
    emits: ['show-chat']
};
</script>

