<template>
    <Head :title="title"/>
    <div class="register-container">
        <div class="register-header">
            <h1>Telega</h1>
            <p>Register to join the Telega</p>
        </div>

        <form @submit.prevent="store" id="registerForm" class="register-form">
            <div class="form-group">
                <label for="name">Username</label>
                <input v-model="form.username" type="text" id="username" name="username" autocomplete="username" autofocus>
                <span class="error-message" v-if="form.errors.username">{{ form.errors.username }}</span>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input v-model="form.phone" type="tel" id="phone" name="phone" autocomplete="tel">
                <span class="error-message" v-if="form.errors.phone">{{ form.errors.phone }}</span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input v-model="form.password" type="password" id="password" name="password" autocomplete="new-password">
                <span class="error-message" v-if="form.errors.password">{{ form.errors.password }}</span>
            </div>

            <div class="form-group">
                <label for="password-confirm">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" id="password-confirm" name="password_confirmation" autocomplete="new-password">
                <span class="error-message" v-if="form.errors.password_confirmation">{{form.errors.password_confirmation}}</span>

            </div>
            <span class="error-message" v-if="form.errors.server">{{form.errors.server}}</span>

            <button type="submit" class="register-button">Register</button>

            <div class="login-link">
                Already have an account? <Link :href="route('authorization-form')">Log in</Link>
            </div>
        </form>
    </div>
</template>

<script>
import {Head, useForm,Link} from "@inertiajs/vue3";
export default {
    components:{
        Head,Link
    },
    props:{
        title:String
    },
    setup(){
        const form = useForm({
            phone:null,
            password:null,
            password_confirmation:null,
            username:null
        })
        function store(){
            form.post(route('register'))
        }
        return {form,store}
    }
}
</script>
<style src="../../../css/User/register.css"></style>
