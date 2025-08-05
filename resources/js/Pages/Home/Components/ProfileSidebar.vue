<template>
    <div class="profile" v-show="isOpen">
        <div class="sidebar-avatar-and-username">
            <div class="avatar-container">
                <img v-if="userData.avatar_url" :src="userData.avatar_url" :alt="userData.username" class="user-avatar"/>
                <div v-else class="fake-user-profile-avatar">{{userData.username[0]}}</div>
                <label class="avatar-upload">
                    <input type="file" name="avatar_url" class="update-avatar" accept="image/*" @change="avatarUpload">
                    <span class="upload-icon">+</span>
                    <span v-if="updateProfileErrors.avatar_url" class="error-message">
                        {{ updateProfileErrors.avatar_url[0] }}
                    </span>
                </label>
            </div>
            <h3 class="username">{{userData.username}}</h3>
        </div>
        <form class="update_profile_data" @submit.prevent="updateProfileData">
            <div>
                <label for="username">username</label>
                <input v-model="userData.username" name="username"/>
                <span v-if="updateProfileErrors.username" class="error-message">
                    {{ updateProfileErrors.username[0] }}
                </span>
            </div>
            <div>
                <label for="phone">phone</label>
                <input v-model="userData.phone" name="phone"/>
                <span v-if="updateProfileErrors.phone" class="error-message">
                    {{ updateProfileErrors.phone[0] }}
                </span>
            </div>
            <div class="bio-div">
                <label for="bio">bio</label>
                <textarea class="bio" v-model="userData.bio" name="bio"/>
                <span v-if="updateProfileErrors.bio" class="error-message">
                    {{ updateProfileErrors.bio[0] }}
                </span>
            </div>
            <button type="submit">update</button>
        </form>
    </div>
</template>

<script>
export default {
    props: {
        isOpen: Boolean,
        initialUserData: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            userData: {...this.initialUserData},
            updateProfileErrors: {},
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || ''
        };
    },
    methods: {
        async updateProfileData(e) {
            const formData = new FormData(e.target);
            const formDataObj = Object.fromEntries(formData);

            const { avatar_url, ...dataToSend } = formDataObj;
            this.updateProfileErrors = {};
            try {
                const response = await fetch(route('update-profile'), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(dataToSend)
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    if (errorData.errors) {
                        this.updateProfileErrors = errorData.errors;
                    } else {
                        throw new Error(errorData.message || 'Update failed');
                    }
                    return;
                }

                const result = await response.json();
                this.userData = {...this.userData, ...result};
                this.$emit('profile-updated', this.userData);
            } catch (error) {
                console.error(error);
            }
        },
        async avatarUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.updateProfileErrors = {};

            const formData = new FormData();
            formData.append('avatar_url', file);

            try {
                const response = await fetch(route('update-avatar'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'include'
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    if (errorData.errors) {
                        this.updateProfileErrors = errorData.errors;
                    } else {
                        throw new Error(errorData.message || 'Update failed');
                    }
                    return;
                }

                const result = await response.json();
                this.userData = {...this.userData, ...result};
                this.$emit('profile-updated', this.userData);
            } catch (error) {
                console.error('Ошибка при загрузке аватара:', error);
            }
        }
    },
    watch: {
        initialUserData(newVal) {
            this.userData = {...newVal};
        }
    }
};
</script>
