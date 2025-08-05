<template>
    <transition name="slide">
        <aside v-show="visible" class="sidebar" @click.stop>
            <div class="sidebar-avatar-and-username">
                <img v-if="userData.avatar_url" :src="userData.avatar_url" :alt="userData.username" class="user-avatar" />
                <div v-else class="fake-user-avatar">{{ userData.username[0] }}</div>
                <h3 class="username">{{ userData.username }}</h3>
                <h3 class="bio-text">{{ userData.bio }}</h3>
            </div>
            <div class="sidebar-active">
                <button @click="$emit('open-profile')">My profile</button>
                <button>Settings</button>
            </div>
        </aside>
    </transition>
</template>

<script>
export default {
    props: {
        visible: Boolean,
        userData: Object
    },
    emits: ['open-profile'],
}
</script>
