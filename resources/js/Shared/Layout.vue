<template>
    <div class="container">
        <slot/>
    </div>
</template>

<script>
import {Link} from "@inertiajs/vue3";
export default {
    components:{
        Link
    }
};
</script>
