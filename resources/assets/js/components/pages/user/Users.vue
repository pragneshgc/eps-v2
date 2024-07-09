<template>
    <div class="content">
        <section class="card">
            <div class="card-header">
                <h3>Users</h3>
            </div>
            <div class="card-body">
                <button class="btn btnSize01 secondaryBtn mb-10" @click="$router.push('/users/new')">
                    Add new user
                </button>

                <DataTable data-url="/users" column-class="col-lg-12" table-title="Users" redirect-name="user"
                    redirect-id="ID" :hidden-columns="['ID', 'Code']" deleteUrl="/users" deleteId="ID"></DataTable>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    data: function () {
        return {
            data: {},
            loading: false
        }
    },
    mounted() {
    }
}
</script>
