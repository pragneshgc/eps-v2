<template>
    <div class="sidebarWrapper" :class="{ 'collapsed': !sidebarVisible }">
        <ul class="sidebar">
            <li class="sidebarSection parentActive">
                <a href="javascript:;" class="mainControl">Main Control</a>
                <ul class="nav">
                    <li>
                        <router-link to="/" class="sidebar-link">
                            <i class="fa fa-home"></i>
                            Home
                        </router-link>
                    </li>

                    <li>
                        <router-link v-if="userInfo.role >= 30" to="/users" class="sidebar-link">
                            <i class="fa fa-users"></i>
                            Users
                        </router-link>
                    </li>

                    <li>
                        <router-link v-if="userInfo.role >= 50" to="/pharmacies" class="sidebar-link">
                            <i class="fa fa-plus-square"></i>
                            Pharmacies
                        </router-link>
                    </li>
                    <li>
                        <router-link title="Clients" v-if="userInfo.role >= 50" to="/clients" class="sidebar-link">
                            <i class="fa fa-address-book-o"></i>
                            Clients
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/orders" class="sidebar-link">
                            <i class="fa fa-cubes"></i>
                            Orders
                        </router-link>
                    </li>
                </ul>
            </li>
        </ul>
        <a @click="$emit('sidebartoggle')" href="javascript:;" class="collapseToggler">Collapse menu</a>
    </div>
</template>
<script>
export default {
    props: ['sidebarVisible'],
    data: function () {
        return {
            userInfo: userInfo,
        }
    },
}
</script>
