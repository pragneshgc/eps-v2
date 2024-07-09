<template>
    <header>
        <div class="logo" style="background-size: contain;">
            <a href="/">
                <img style="height: 100%;" :src="`/pharmacies/logo/${userInfo.pharmacy_id}`" alt="">
            </a>
        </div>

        <div class="user">
            <ul>
                <li>
                    <a href="javascript:;">
                        <span>
                            <i class="fa fa-user"></i>
                            <b>{{ userInfo.name }} {{ userInfo.surname }}</b>
                        </span>
                    </a>
                </li>

                <li class="big-icon" title="App Info" style="padding: 0px;">
                    <router-link style="padding: 0 10px;" to="/info">
                        <i class="fa fa-info"></i>
                    </router-link>
                </li>

                <li class="big-icon" title="Logout" style="padding: 0px 5px;">
                    <a href="/logout">
                        <!-- <span class="clickable"> -->
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <!-- </span>                 -->
                    </a>
                </li>
            </ul>
        </div>
    </header>
</template>
<script>
export default {
    data: function () {
        return {
            userInfo: userInfo,
        }
    },
}
</script>
