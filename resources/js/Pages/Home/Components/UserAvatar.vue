<template>
    <div class="avatar-container">
        <img
            v-if="user.avatar_url"
            :src="user.avatar_url"
            :alt="user.username"
            class="user-avatar"
            :class="{
                'small-avatar': size === 'small',
                'medium-avatar': size === 'medium',
                'large-avatar': size === 'large'
            }"
        />
        <div
            v-else
            class="fake-user-avatar"
            :class="{
                'small-avatar': size === 'small',
                'medium-avatar': size === 'medium',
                'large-avatar': size === 'large'
            }"
        >
            {{ user.username ? user.username[0] : '' }}
        </div>
        <label class="avatar-upload" v-if="editable">
            <input
                type="file"
                name="avatar_url"
                class="update-avatar"
                accept="image/*"
                @change="$emit('update-avatar', $event)"
            >
            <span class="upload-icon">+</span>
        </label>
        <span v-if="error" class="error-message">{{ error }}</span>
    </div>
</template>

<script>
export default {
    props: {
        user: Object,
        size: {
            type: String,
            default: 'medium',
            validator: value => ['small', 'medium', 'large'].includes(value)
        },
        editable: Boolean,
        error: String
    },
    emits: ['update-avatar']
};
</script>
