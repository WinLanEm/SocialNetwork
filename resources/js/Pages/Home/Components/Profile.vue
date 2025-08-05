<template>
    <div class="profile">
        <div class="sidebar-avatar-and-username">
            <div class="avatar-container">
                <img v-if="userData.avatar_url" :src="userData.avatar_url" :alt="userData.username" class="user-avatar" />
                <div v-else class="fake-user-profile-avatar">{{ userData.username[0] }}</div>
                <label class="avatar-upload">
                    <input type="file" name="avatar_url" class="update-avatar" accept="image/*" @change="avatarUpload" />
                    <span class="upload-icon">+</span>
                    <span v-if="errors.avatar_url" class="error-message">{{ errors.avatar_url[0] }}</span>
                </label>
            </div>
            <h3 class="username">{{ userData.username }}</h3>
        </div>

        <form class="update_profile_data" @submit.prevent="updateProfileData">
            <div>
                <label for="username">username</label>
                <input :value="userData.username" name="username" />
                <span v-if="errors.username" class="error-message">{{ errors.username[0] }}</span>
            </div>
            <div>
                <label for="phone">phone</label>
                <input name="phone" :value="userData.phone" />
                <span v-if="errors.phone" class="error-message">{{ errors.phone[0] }}</span>
            </div>
            <div class="bio-div">
                <label for="bio">bio</label>
                <textarea class="bio" name="bio" :value="userData.bio" />
                <span v-if="errors.bio" class="error-message">{{ errors.bio[0] }}</span>
            </div>

            <button type="submit">update</button>
        </form>
    </div>
</template>

<script>
export default {
    props: {
        userData: Object,
        errors: Object
    },
    emits: ['avatar-upload', 'update-profile'],
    methods: {
        avatarUpload(event) {
            this.$emit('avatar-upload', event);
        },
        updateProfileData(event) {
            this.$emit('update-profile', event);
        }
    }
}
</script>
