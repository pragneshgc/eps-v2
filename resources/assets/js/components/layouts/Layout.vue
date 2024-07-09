<template>
    <div>
        <Header />
        <Sidebar @sidebartoggle="toggleSidebar()" :sidebarVisible="sidebarVisible" />
        <Content :sidebarVisible="sidebarVisible" />
    </div>
</template>

<script>
import Header from './Header.vue'
import Sidebar from './Sidebar.vue'
import Content from './Content.vue'

export default {
    data: function () {
        return {
            sidebarVisible: true,
        }
    },
    components: {
        Header, Sidebar, Content
    },
    computed: {
        isDemo() {
            return window.location.origin != 'https://eps.delivery'
        }
    },
    methods: {
        toggleSidebar() {
            if (this.sidebarVisible === true) {
                this.sidebarVisible = false;
            } else {
                this.sidebarVisible = true;
            }
        },
    },
}
</script>
