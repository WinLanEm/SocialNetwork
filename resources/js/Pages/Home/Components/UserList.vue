<template>
    <ul class="members-searched">
        <li
            v-for="user in users"
            :key="user.id"
            @mousedown.prevent
            @click="$emit('userSelected', user)"
        >
            <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.username" />
            <div v-else class="fake-avatar">{{ user.username[0] }}</div>
            <div class="name_and_last_seen">
                <h3>{{ user.username }}</h3>
                <h6 v-if="user.last_seen">{{ user.last_seen }}</h6>
            </div>
        </li>
    </ul>
</template>

<script>
export default {
    props: ['users'],
};
</script>
