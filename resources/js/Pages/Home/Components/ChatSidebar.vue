<template>
    <aside class="sidebar" @click.stop>
        <div class="sidebar-avatar-and-username">
            <UserAvatar :user="userData" size="medium" />
            <h3 class="username">{{userData.username}}</h3>
            <h3 class="bio-text">{{userData.bio}}</h3>
        </div>
        <div class="sidebar-active">
            <button @click="$emit('open-profile')">My profile</button>
            <button>Settings</button>
        </div>
    </aside>
</template>

<script>
import UserAvatar from './UserAvatar.vue';

export default {
    components: { UserAvatar },
    props: {
        userData: Object
    },
    emits: ['open-profile', 'close']
};
</script>
