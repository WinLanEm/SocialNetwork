<template>
    <Head :title="title"/>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Welcome back</h1>
            <p>Sign in to continue to Telega</p>
        </div>

        <form @submit.prevent="store" class="auth-form">
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input v-model="form.phone" type="tel" id="phone" name="phone" autocomplete="tel">
                <span class="error-message" v-if="form.errors.phone">{{ form.errors.phone }}</span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input v-model="form.password" type="password" id="password" name="password" autocomplete="current-password">
                <span class="error-message" v-if="form.errors.password">{{ form.errors.password }}</span>
            </div>

            <button type="submit" class="auth-button">Sign In</button>

            <div class="auth-footer">
                <span>Don't have an account? <Link class="register-link" :href="route('register-form')">Register</Link></span>
            </div>
        </form>
    </div>
</template>

<script>
import {Link, Head, useForm} from "@inertiajs/vue3";
export default {
    components: {
        Link, Head
    },
    props:{
        title:String
    },
    setup(){
        const form = useForm({
            phone:null,
            password:null
        })
        function store(){
            form.post(route('authorization'));
        }
        return {form,store};
    }
}
</script>
<style src="../../../css/User/authorization.css"></style>
