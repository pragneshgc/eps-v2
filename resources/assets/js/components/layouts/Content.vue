<template>
    <div class="contentWrapper" :class="{ 'fullWidth': !sidebarVisible }">
        <Online />
        <RouterView />
    </div>
</template>

<script>
import Online from '../Online.vue';

export default {
    props: ['sidebarVisible'],
    components: {
        Online
    },
}
</script>
